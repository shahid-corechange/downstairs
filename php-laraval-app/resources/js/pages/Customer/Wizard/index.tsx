import { Head, router } from "@inertiajs/react";
import { useRef } from "react";
import { useTranslation } from "react-i18next";

import Wizard from "@/components/Wizard";
import WizardStep from "@/components/Wizard/components/WizardStep";
import { WizardState } from "@/components/Wizard/types";

import MainLayout from "@/layouts/Main";

import { getMeta } from "@/services/meta";

import { handleWizardErrorNavigation } from "@/utils/wizard";

import AccountStep from "./components/AccountStep";
import PrimaryAddressStep from "./components/PrimaryAddressStep";
import PropertyStep from "./components/PropertyStep";
import { StepsValues } from "./types";

const WizardPage = () => {
  const { t } = useTranslation();

  const wizardRef = useRef<WizardState>(null);

  const handleWizardFinish = (
    stepsValues: StepsValues,
    toggleFinish: () => void,
  ) => {
    const data = {
      ...stepsValues[0],
      ...stepsValues[1],
      ...stepsValues[2],
      // Step 3 is actually property step but because the invoice step is temporarily disabled, we need to skip it
      // ...stepsValues[3],
    };

    const payload = {
      ...data,
      propertyMeta: getMeta(["note"], data),
      keyInformation: {
        keyPlace: data.keyPlace,
        frontDoorCode: data.frontDoorCode,
        alarmCodeOff: data.alarmCodeOff,
        alarmCodeOn: data.alarmCodeOn,
        information: data.information,
      },
    };

    router.post("/customers/wizard", payload, {
      onFinish: toggleFinish,
      onSuccess: () => {
        router.get("/customers/subscriptions/wizard");
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
      <MainLayout>
        <Wizard
          ref={wizardRef}
          maxW="4xl"
          onFinish={(values, toggle) =>
            handleWizardFinish(values as StepsValues, toggle)
          }
        >
          <WizardStep
            title={t("account")}
            description={t("customer account text")}
          >
            <AccountStep />
          </WizardStep>
          <WizardStep
            title={t("primary address")}
            description={t("customer primary address text")}
            // skipTo={3} - InvoiceAddress step is temporarily disabled until UI adjustment
            // skipLabel={t("skip to property")} - InvoiceAddress step is temporarily disabled until UI adjustment
          >
            <PrimaryAddressStep />
          </WizardStep>
          {/* Invoice address step is not used for now, maybe we will add it again after UI adjustment */}
          {/* <WizardStep
            title={t("invoice address")}
            description={t("customer invoice address text")}
          >
            <InvoiceAddressStep />
          </WizardStep> */}
          <WizardStep
            title={t("property")}
            description={t("customer property text")}
          >
            <PropertyStep />
          </WizardStep>
        </Wizard>
      </MainLayout>
    </>
  );
};

export default WizardPage;
