import { Button, Flex, useDisclosure } from "@chakra-ui/react";
import { Dayjs } from "dayjs";
import { useRef, useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import Modal from "@/components/Modal";
import ScheduleCollisionModal from "@/components/ScheduleCollisionModal";
import Wizard from "@/components/Wizard";
import WizardStep from "@/components/Wizard/components/WizardStep";
import { WizardState } from "@/components/Wizard/types";

import { DATE_FORMAT, TIME_FORMAT } from "@/constants/datetime";

import { useError } from "@/hooks/error";

import useScheduleStore from "@/pages/Schedule/Overview/store";

import { useCreateHistoricalScheduleMutation } from "@/services/schedule";

import Schedule from "@/types/schedule";
import Team from "@/types/team";

import { formatTime } from "@/utils/datetime";
import { handleWizardErrorNavigation } from "@/utils/wizard";

import PlanStep from "./components/PlanStep";
import TimeStep from "./components/TimeStep";
import WorkerStep from "./components/WorkerStep";
import { StepsValues } from "./types";

interface CreateHistoricalScheduleModalProps {
  team: Team;
  startAt: Dayjs;
  isOpen: boolean;
  onClose: () => void;
}

const CreateHistoricalScheduleModal = ({
  team,
  startAt,
  isOpen,
  onClose,
}: CreateHistoricalScheduleModalProps) => {
  const { t } = useTranslation();
  const {
    isOpen: isWizardOpen,
    onOpen: onOpenWizard,
    onClose: onCloseWizard,
  } = useDisclosure();
  const { getErrors } = useError();

  const addSchedule = useScheduleStore((state) => state.addSchedule);
  const createHistoricalScheduleMutation =
    useCreateHistoricalScheduleMutation();

  const [customerType, setCustomerType] = useState<"private" | "company">(
    "private",
  );
  const [collidedSchedules, setCollidedSchedules] = useState<Schedule[]>([]);

  const wizardRef = useRef<WizardState>(null);

  const handleOpenWizard = (type: "private" | "company") => {
    setCustomerType(type);
    onOpenWizard();
  };

  const handleCloseWizard = () => {
    onCloseWizard();
    onClose();
  };

  const handleWizardFinish = (
    stepsValues: StepsValues,
    toggleFinish: () => void,
  ) => {
    const {
      userId,
      customerId,
      propertyId,
      serviceId,
      quarters,
      description,
      addonIds,
      calculatedPrice,
      utcStartAt,
      totalPrice: price,
    } = stepsValues[0];
    const { attendances } = stepsValues[1];

    const parsedAddonIds = addonIds ? JSON.parse(addonIds) : [];
    const totalPrice = calculatedPrice !== price ? price : undefined;
    const startAt = utcStartAt.format(DATE_FORMAT);
    const startTimeAt = utcStartAt.format(TIME_FORMAT);

    createHistoricalScheduleMutation.mutate(
      {
        userId,
        customerId,
        propertyId,
        teamId: team.id,
        serviceId,
        quarters,
        description,
        totalPrice,
        startAt,
        startTimeAt,
        addonIds: parsedAddonIds,
        workers: attendances,
      },
      {
        onSettled: () => {
          toggleFinish();
        },
        onSuccess: ({ data }) => {
          handleCloseWizard();
          addSchedule(data);
        },
        onError: () => {
          const { type, validationError, error } = getErrors<{
            collisions?: Schedule[];
          }>();

          if (type === "validation") {
            handleWizardErrorNavigation(
              validationError,
              stepsValues,
              wizardRef,
            );
            return;
          }

          if (error.collisions) {
            setCollidedSchedules(error.collisions);
            return;
          }
        },
      },
    );
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
            description={t("historical schedule plan description")}
          >
            <PlanStep
              customerType={customerType}
              team={team}
              startAt={startAt}
            />
          </WizardStep>
          <WizardStep
            title={t("workers")}
            description={t("historical schedule workers description")}
          >
            <WorkerStep team={team} startAt={startAt} />
          </WizardStep>
          <WizardStep
            title={t("time")}
            description={t("historical schedule time description")}
          >
            <TimeStep />
          </WizardStep>
        </Wizard>
      </Modal>
      <Modal isOpen={isOpen && !isWizardOpen} onClose={onClose} size="md">
        <Trans
          i18nKey="modal create schedule without endAt"
          values={{
            startAt: formatTime(startAt),
            team: team.name,
          }}
        />
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

export default CreateHistoricalScheduleModal;
