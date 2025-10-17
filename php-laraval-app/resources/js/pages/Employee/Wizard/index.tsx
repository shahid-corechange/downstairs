import { Head, router } from "@inertiajs/react";
import { useRef } from "react";
import { useTranslation } from "react-i18next";

import Wizard from "@/components/Wizard";
import WizardStep from "@/components/Wizard/components/WizardStep";
import { WizardState } from "@/components/Wizard/types";

import MainLayout from "@/layouts/Main";

import { handleWizardErrorNavigation } from "@/utils/wizard";

import AccountStep from "./components/AccountStep";
import AddressStep from "./components/AddressStep";
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
    };

    const payload = {
      ...data,
      roles: data.roles ? JSON.parse(data.roles) : [],
    };

    router.post("/employees/wizard", payload, {
      onFinish: toggleFinish,
      onSuccess: () => {
        router.get("/employees");
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
            description={t("employee wizard account text")}
          >
            <AccountStep />
          </WizardStep>
          <WizardStep
            title={t("address")}
            description={t("employee wizard address text")}
          >
            <AddressStep />
          </WizardStep>
        </Wizard>
      </MainLayout>
    </>
  );
};

export default WizardPage;
