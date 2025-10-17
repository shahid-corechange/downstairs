import { Page } from "@inertiajs/core";
import { Head, router } from "@inertiajs/react";
import { useRef } from "react";
import { useTranslation } from "react-i18next";

import Wizard from "@/components/Wizard";
import WizardStep from "@/components/Wizard/components/WizardStep";
import { WizardState } from "@/components/Wizard/types";

import CashierLayout from "@/layouts/Cashier";

import { getMeta } from "@/services/meta";

import { handleWizardErrorNavigation } from "@/utils/wizard";

import { PageProps } from "@/types";

import AccountStep from "./components/AccountStep";
import ContactStep from "./components/ContactStep";
import PrimaryAddressStep from "./components/PrimaryAddressStep";
import { StepsValues } from "./types";

interface SuccessPayload {
  userId: number;
}

const CompanyWizard = () => {
  const { t } = useTranslation();

  const wizardRef = useRef<WizardState>(null);

  const handleWizardFinish = (
    stepsValues: StepsValues,
    toggleFinish: () => void,
  ) => {
    const data = {
      ...{ membershipType: "company" },
      ...stepsValues[0],
      ...stepsValues[1],
      ...stepsValues[2],
    };

    const payload = {
      ...data,
      propertyMeta: getMeta(["note"], data),
    };

    router.post("/cashier/customers/company", payload, {
      onFinish: toggleFinish,
      onSuccess: (page) => {
        const {
          flash: { successPayload },
        } = (
          page as Page<
            PageProps<
              Record<string, unknown>,
              SuccessPayload | undefined,
              unknown
            >
          >
        ).props;

        const userId = successPayload?.userId;

        if (!userId) {
          router.get("/cashier/search");
        }

        router.get(`/cashier/customers/${userId}/cart`);
      },
      onError: (errors) => {
        handleWizardErrorNavigation(errors, stepsValues, wizardRef);
      },
    });
  };

  return (
    <>
      <Head>
        <title>{t("wizard")}</title>
      </Head>
      <CashierLayout>
        <Wizard
          ref={wizardRef}
          maxW="4xl"
          onFinish={(values, toggle) =>
            handleWizardFinish(values as StepsValues, toggle)
          }
        >
          <WizardStep
            title={t("account")}
            description={t("company account text")}
          >
            <AccountStep />
          </WizardStep>
          <WizardStep
            title={t("contact")}
            description={t("company contact text")}
          >
            <ContactStep />
          </WizardStep>
          <WizardStep
            title={t("primary address")}
            description={t("company primary address text")}
          >
            <PrimaryAddressStep />
          </WizardStep>
        </Wizard>
      </CashierLayout>
    </>
  );
};

export default CompanyWizard;
