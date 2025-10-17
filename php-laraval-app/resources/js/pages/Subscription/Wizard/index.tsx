import { Page } from "@inertiajs/core";
import { Head, router } from "@inertiajs/react";
import { useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";
import ScheduleCollisionModal from "@/components/ScheduleCollisionModal";
import Wizard from "@/components/Wizard";
import WizardStep from "@/components/Wizard/components/WizardStep";
import { WizardState } from "@/components/Wizard/types";

import { DATE_FORMAT, TIME_FORMAT } from "@/constants/datetime";

import MainLayout from "@/layouts/Main";

import Schedule from "@/types/schedule";

import { toDayjs } from "@/utils/datetime";
import { handleWizardErrorNavigation } from "@/utils/wizard";

import { PageProps } from "@/types";

import PlanStep from "./components/PlanStep";
import TimeStep from "./components/TimeStep";
import { StepsValues, WizardRequestPayload } from "./types";

const WizardPage = ({ query }: PageProps) => {
  const { t } = useTranslation();

  const wizardRef = useRef<WizardState>(null);

  const [isConfirmationModalOpen, setIsConfirmationModalOpen] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const onConfirmationModalClose = () => {
    const toggleFinish = wizardRef.current?.toggleFinish;
    if (!toggleFinish) {
      return;
    }

    toggleFinish();
    setIsConfirmationModalOpen(false);
  };

  const [collidedSchedules, setCollidedSchedules] = useState<Schedule[]>([]);

  const handleWizardFinish = (values: StepsValues) => {
    const teamId = values[0]?.teamId;
    if (!teamId) {
      setIsConfirmationModalOpen(true);
      return;
    }

    handleSubmit();
  };

  const handleSubmit = () => {
    const stepsValues = wizardRef.current?.stepsValues as StepsValues;
    const toggleFinish = wizardRef.current?.toggleFinish;

    if (!stepsValues || !toggleFinish) {
      return;
    }

    setIsSubmitting(true);

    const {
      userId,
      customerId,
      propertyId,
      teamId,
      calculatedPrice,
      totalPrice: stepTotalPrice,
      serviceId,
      addonIds,
      fixedPriceId,
      description,
      quarters,
    } = stepsValues[0];

    const { isFixed, frequency, utcStartAt, endAt } = stepsValues[1];

    const totalPrice =
      calculatedPrice !== stepTotalPrice ? stepTotalPrice : undefined;

    const payload: WizardRequestPayload = {
      userId,
      customerId,
      serviceId,
      products: [], // TODO: implement products
      addonIds: addonIds ? JSON.parse(addonIds) : [],
      fixedPriceId: fixedPriceId || undefined,
      description,
      isFixed: isFixed === "true",
      frequency,
      startAt: utcStartAt.format(DATE_FORMAT),
      endAt: frequency === 0 ? null : endAt,
      ...(teamId
        ? {
            totalPrice,
          }
        : {
            type: "private",
            fixedPrice: totalPrice,
          }),
      cleaningDetail: {
        propertyId,
        quarters,
        startTime: utcStartAt.format(TIME_FORMAT),
        ...(teamId && { teamId }),
      },
    };

    const url = !teamId
      ? "/unassign-subscriptions"
      : "/customers/subscriptions/wizard";

    router.post(url, payload, {
      onFinish: () => {
        toggleFinish();
        setIsSubmitting(false);
        setIsConfirmationModalOpen(false);
      },
      onSuccess: (page) => {
        const {
          flash: { error, errorPayload },
        } = (
          page as Page<PageProps<Record<string, unknown>, unknown, Schedule[]>>
        ).props;

        if (error) {
          setCollidedSchedules(errorPayload ?? []);
          return;
        }

        if (query?.startAt) {
          const startAt = toDayjs(query?.startAt)
            .weekday(0)
            .format(DATE_FORMAT);
          const endAt = toDayjs(query?.startAt)
            .weekday(6)
            .format(DATE_FORMAT);
          router.get(`/schedules?startAt.gte=${startAt}&endAt.lte=${endAt}`);
          return;
        }

        const redirectUrl = !teamId
          ? "/unassign-subscriptions"
          : "/customers/subscriptions";

        router.get(redirectUrl);
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
          onFinish={(values) => handleWizardFinish(values as StepsValues)}
        >
          <WizardStep
            title={t("subscription plan")}
            description={t("subscription wizard data text")}
          >
            <PlanStep />
          </WizardStep>
          <WizardStep
            title={t("frequency and time")}
            description={t("subscription wizard frequency and time text")}
          >
            <TimeStep />
          </WizardStep>
        </Wizard>
      </MainLayout>
      <AlertDialog
        title={t("create unassign subscription")}
        confirmButton={{
          isLoading: isSubmitting,
          loadingText: t("please wait"),
        }}
        confirmText={t("create")}
        isOpen={isConfirmationModalOpen}
        onClose={onConfirmationModalClose}
        onConfirm={handleSubmit}
      >
        {t("create unassign subscription alert body")}
      </AlertDialog>
      <ScheduleCollisionModal
        isOpen={collidedSchedules.length > 0}
        onClose={() => setCollidedSchedules([])}
        data={collidedSchedules}
      />
    </>
  );
};

export default WizardPage;
