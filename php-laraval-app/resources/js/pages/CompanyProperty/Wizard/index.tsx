import { Head, router } from "@inertiajs/react";
import { useRef } from "react";
import { useTranslation } from "react-i18next";

import Wizard from "@/components/Wizard";
import WizardStep from "@/components/Wizard/components/WizardStep";
import { WizardState } from "@/components/Wizard/types";

import MainLayout from "@/layouts/Main";

import { getMeta } from "@/services/meta";

import { handleWizardErrorNavigation } from "@/utils/wizard";

import AddressStep from "./components/AddressStep";
import KeyStep from "./components/KeyStep";
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
    };

    const payload = {
      ...data,
      meta: getMeta(["note"], data),
      keyInformation: {
        keyPlace: data.keyPlace,
        frontDoorCode: data.frontDoorCode,
        alarmCodeOff: data.alarmCodeOff,
        alarmCodeOn: data.alarmCodeOn,
        information: data.information,
      },
    };

    router.post("/companies/properties/wizard", payload, {
      onFinish: toggleFinish,
      onSuccess: () => {
        router.get("/companies/properties");
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
            title={t("address")}
            description={t("property wizard address text")}
          >
            <AddressStep />
          </WizardStep>
          <WizardStep
            title={t("property")}
            description={t("property wizard property text")}
          >
            <PropertyStep />
          </WizardStep>
          <WizardStep
            title={t("key")}
            description={t("property wizard key information text")}
          >
            <KeyStep />
          </WizardStep>
        </Wizard>
      </MainLayout>
    </>
  );
};

export default WizardPage;
