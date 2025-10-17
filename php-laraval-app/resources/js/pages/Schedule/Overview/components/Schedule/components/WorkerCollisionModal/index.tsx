import {
  Button,
  ButtonProps,
  Flex,
  Tab,
  TabList,
  TabPanels,
  Tabs,
} from "@chakra-ui/react";
import { Dayjs } from "dayjs";
import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";

import Modal from "@/components/Modal";
import { ModalExpansion } from "@/components/Modal/types";

import { useError } from "@/hooks/error";

import { WorkerCollisionError } from "@/pages/Schedule/Overview/types";

import { useBulkChangeWorkersMutation } from "@/services/schedule";

import ScheduleEmployee from "@/types/scheduleEmployee";

import OtherScheduleWorkerPanel from "./components/OtherScheduleWorkerPanel";
import ScheduleWorkerPanel from "./components/ScheduleWorkerPanel";
import { ChangedWorkers } from "./types";

interface WorkerCollisionModalProps {
  startAt: string | Dayjs;
  endAt: string | Dayjs;
  isOpen: boolean;
  onSubmit: () => void;
  onClose: () => void;
  submitButtonLabel?: string;
  submitButtonProps?: Omit<ButtonProps, "isLoading" | "isDisabled" | "onClick">;
  closeButtonLabel?: string;
  closeButtonProps?: Omit<ButtonProps, "onClick">;
  scheduleId?: number;
}

const WorkerCollisionModal = ({
  startAt,
  endAt,
  scheduleId,
  isOpen,
  submitButtonLabel,
  submitButtonProps,
  closeButtonLabel,
  closeButtonProps,
  onSubmit,
  onClose,
}: WorkerCollisionModalProps) => {
  const { t } = useTranslation();

  const { consumeErrors } = useError();
  const bulkChangeWorkersMutation = useBulkChangeWorkersMutation();

  const [isLoading, setIsLoading] = useState(false);
  const [isExpanded, setIsExpanded] = useState(false);
  const [isAssignedWorkers, setIsAssignedWorkers] = useState(false);
  const [expandableContent, setExpandableContent] = useState<React.ReactNode>();
  const [expandableTitle, setExpandableTitle] = useState<string>();
  const [scheduleWorkerIds, setScheduleWorkerIds] = useState<number[]>([]);
  const [scheduleCollidedWorkers, setScheduleCollidedWorkers] = useState<
    ScheduleEmployee[]
  >([]);
  const [workerCollisions, setWorkerCollisions] = useState<ScheduleEmployee[]>(
    [],
  );
  const [changedWorkers, setChangedWorkers] = useState<ChangedWorkers[]>([]);

  const handleModalExpansion = (expansion: ModalExpansion) => {
    setIsExpanded(true);
    setExpandableContent(expansion.content);
    setExpandableTitle(expansion.title);
  };

  const handleShrink = () => {
    setIsExpanded(false);
    setExpandableContent(undefined);
    setExpandableTitle(undefined);
  };

  const handleChangeWorker = (
    source: "current" | "other",
    worker: ScheduleEmployee,
    userId: number,
  ) => {
    setChangedWorkers((prev) => [
      ...prev,
      { scheduleEmployeeId: worker.id, scheduleId: worker.scheduleId, userId },
    ]);

    if (source === "current") {
      setScheduleCollidedWorkers((prev) =>
        prev.filter((item) => item.id !== worker.id),
      );
      setWorkerCollisions((prev) =>
        prev.filter((item) => item.userId !== worker.userId),
      );
      return;
    }

    setWorkerCollisions((prev) => prev.filter((item) => item.id !== worker.id));

    if (
      workerCollisions.filter((item) => item.userId === worker.userId)
        .length === 1
    ) {
      setScheduleCollidedWorkers((prev) =>
        prev.filter((item) => item.userId !== worker.userId),
      );
    }
  };

  const handleSubmit = () => {
    setIsLoading(true);

    bulkChangeWorkersMutation.mutate(
      {
        changes: changedWorkers,
      },
      {
        onSuccess: () => {
          onSubmit();
        },
        onError: () => {
          setIsLoading(false);
        },
      },
    );
  };

  useEffect(() => {
    if (!isOpen) {
      return;
    }

    const { type, error } = consumeErrors<Partial<WorkerCollisionError>>();

    if (type !== "other" || !error.workerCollisions) {
      return;
    }

    const scheduleCollidedWorkers = error.scheduleCollidedWorkers ?? [];

    setIsAssignedWorkers(scheduleCollidedWorkers.length > 0);
    setScheduleWorkerIds(error.scheduleWorkerIds ?? []);
    setScheduleCollidedWorkers(scheduleCollidedWorkers);
    setWorkerCollisions(error.workerCollisions);

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen]);

  return (
    <Modal
      size="5xl"
      bodyContainer={{ p: 8 }}
      title={t("collided workers")}
      expandableContent={expandableContent}
      expandableTitle={expandableTitle}
      isOpen={isOpen}
      isExpanded={isExpanded}
      onClose={onClose}
      onShrink={handleShrink}
    >
      <Tabs>
        <TabList>
          {isAssignedWorkers && <Tab>{t("this booking")}</Tab>}
          <Tab>{t("other bookings")}</Tab>
        </TabList>

        <TabPanels>
          {isAssignedWorkers && (
            <ScheduleWorkerPanel
              scheduleWorkerIds={scheduleWorkerIds}
              changedWorkers={changedWorkers}
              collidedWorkers={scheduleCollidedWorkers}
              startAt={startAt}
              endAt={endAt}
              onChangeWorker={(worker, userId) =>
                handleChangeWorker("current", worker, userId)
              }
              onModalExpansion={handleModalExpansion}
              onModalShrink={handleShrink}
              py={8}
            />
          )}
          <OtherScheduleWorkerPanel
            scheduleWorkerIds={scheduleWorkerIds}
            changedWorkers={changedWorkers}
            collidedWorkers={workerCollisions}
            startAt={startAt}
            endAt={endAt}
            scheduleId={scheduleId}
            onChangeWorker={(worker, userId) =>
              handleChangeWorker("other", worker, userId)
            }
            onModalExpansion={handleModalExpansion}
            onModalShrink={handleShrink}
            py={8}
          />
        </TabPanels>
      </Tabs>
      <Flex justify="right" mt={4} gap={4}>
        <Button
          colorScheme="gray"
          fontSize="sm"
          onClick={onClose}
          {...closeButtonProps}
        >
          {closeButtonLabel || t("close")}
        </Button>
        <Button
          fontSize="sm"
          loadingText={t("please wait")}
          isLoading={isLoading}
          isDisabled={
            scheduleCollidedWorkers.length > 0 || workerCollisions.length > 0
          }
          onClick={handleSubmit}
          {...submitButtonProps}
        >
          {submitButtonLabel || t("submit")}
        </Button>
      </Flex>
    </Modal>
  );
};

export default WorkerCollisionModal;
