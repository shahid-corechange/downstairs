import { Button, Flex, useDisclosure } from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { router } from "@inertiajs/react";
import { useRef, useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import Modal from "@/components/Modal";
import ScheduleCollisionModal from "@/components/ScheduleCollisionModal";
import Wizard from "@/components/Wizard";
import WizardStep from "@/components/Wizard/components/WizardStep";
import { WizardState } from "@/components/Wizard/types";

import { DATE_FORMAT, TIME_FORMAT } from "@/constants/datetime";

import Schedule from "@/types/schedule";

import { handleWizardErrorNavigation } from "@/utils/wizard";

import { PageProps } from "@/types";

import { CreateUnassignSubscriptionPayload, StepsValues } from "../../types";
import PlanStep from "./components/PlanStep";
import TimeStep from "./components/TimeStep";

interface CreateModalProps {
  isOpen: boolean;
  onClose: () => void;
}

const CreateModal = ({ isOpen, onClose }: CreateModalProps) => {
  const { t } = useTranslation();
  const {
    isOpen: isWizardOpen,
    onOpen: onOpenWizard,
    onClose: onCloseWizard,
  } = useDisclosure();

  const [customerType, setCustomerType] = useState<"private" | "company">(
    "private",
  );

  const [collidedSchedules, setCollidedSchedules] = useState<Schedule[]>([]);

  const wizardRef = useRef<WizardState>(null);

  const handleCloseWizard = () => {
    onCloseWizard();
    onClose();
  };

  const handleOpenWizard = (type: "private" | "company") => {
    setCustomerType(type);
    onOpenWizard();
  };

  const handleWizardFinish = (
    stepsValues: StepsValues,
    toggleFinish: () => void,
  ) => {
    const payload: CreateUnassignSubscriptionPayload = {
      type: customerType,
      userId: stepsValues[0].userId,
      customerId: stepsValues[0].customerId,
      serviceId: stepsValues[0].serviceId,
      productCarts: [],
      addonIds: stepsValues[0].addonIds
        ? JSON.parse(stepsValues[0].addonIds)
        : [],
      description: stepsValues[0].description,
      isFixed: stepsValues[1].isFixed === "true",
      frequency: stepsValues[1].frequency,
      startAt: stepsValues[1].utcStartAt.format(DATE_FORMAT),
      endAt: stepsValues[1].frequency === 0 ? null : stepsValues[1].endAt,
      fixedPrice:
        stepsValues[0].calculatedPrice !== stepsValues[0].fixedPrice
          ? stepsValues[0].fixedPrice
          : undefined,
      cleaningDetail: {
        propertyId: stepsValues[0].propertyId,
        quarters: stepsValues[0].quarters,
        startTime: stepsValues[1].utcStartAt.format(TIME_FORMAT),
      },
    };

    router.post("/unassign-subscriptions", payload, {
      onFinish: toggleFinish,
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

        handleCloseWizard();
        router.get("/unassign-subscriptions");
      },
      onError: (errors) => {
        handleWizardErrorNavigation(errors, stepsValues, wizardRef);
      },
    });
  };

  return (
    <>
      <Modal
        isOpen={isWizardOpen}
        onClose={handleCloseWizard}
        contentContainer={{ maxW: "7xl", height: "full" }}
        bodyContainer={{ p: 8 }}
      >
        <Wizard
          ref={wizardRef}
          onFinish={(values, toggle) =>
            handleWizardFinish(values as StepsValues, toggle)
          }
        >
          <WizardStep
            title={t("plan")}
            description={t("unassign subscription plan description")}
          >
            <PlanStep customerType={customerType} />
          </WizardStep>
          <WizardStep
            title={t("time")}
            description={t("unassign subscription time description")}
          >
            <TimeStep />
          </WizardStep>
        </Wizard>
      </Modal>
      <Modal isOpen={isOpen && !isWizardOpen} onClose={onClose} size="md">
        <Trans i18nKey="modal create unassign subscription" />
        <Flex justifyContent="center" mt={8} gap={6}>
          <Button onClick={() => handleOpenWizard("private")}>
            {t("private")}
          </Button>
          <Button onClick={() => handleOpenWizard("company")}>
            {t("company")}
          </Button>
        </Flex>
      </Modal>
      <ScheduleCollisionModal
        isOpen={collidedSchedules.length > 0}
        onClose={() => setCollidedSchedules([])}
        data={collidedSchedules}
      />
    </>
  );
};

export default CreateModal;
