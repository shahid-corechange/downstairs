import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import { useError } from "@/hooks/error";

import WorkerCollisionModal from "@/pages/Schedule/Overview/components/Schedule/components/WorkerCollisionModal";
import { WorkerCollisionError } from "@/pages/Schedule/Overview/types";

import { useChangeScheduleWorkerStatusMutation } from "@/services/schedule";

import { Response } from "@/types/api";
import Schedule from "@/types/schedule";
import ScheduleEmployee from "@/types/scheduleEmployee";

interface ChangeStatusConfirmationProps {
  schedule: Schedule;
  selectedEmployee: ScheduleEmployee;
  isOpen: boolean;
  onClose: () => void;
  onSuccess: (data: Schedule, response: Response<Schedule>) => void;
}

const ChangeStatusConfirmation = ({
  schedule,
  selectedEmployee,
  isOpen,
  onClose,
  onSuccess,
}: ChangeStatusConfirmationProps) => {
  const { t } = useTranslation();

  const changeStatusMutation = useChangeScheduleWorkerStatusMutation();
  const { getErrors } = useError();

  const [isLoading, setIsLoading] = useState(false);
  const [isWorkerColliding, setIsWorkerColliding] = useState(false);

  const handleChangeStatus = () => {
    setIsLoading(true);

    const action = selectedEmployee?.deletedAt ? "enable" : "disable";

    changeStatusMutation.mutate(
      {
        scheduleId: schedule.id,
        userId: selectedEmployee.userId,
        action,
      },
      {
        onSettled: () => {
          setIsLoading(false);
        },
        onSuccess: ({ data, response }) => {
          handleClose();
          onSuccess(data, response);
        },
        onError: () => {
          const { type, error } = getErrors<Partial<WorkerCollisionError>>();

          if (type === "other" && error.workerCollisions) {
            setIsWorkerColliding(true);
          }
        },
      },
    );
  };

  const handleClose = () => {
    setIsWorkerColliding(false);
    onClose();
  };

  return (
    <>
      <AlertDialog
        title={
          selectedEmployee?.deletedAt ? t("enable worker") : t("disable worker")
        }
        confirmText={selectedEmployee?.deletedAt ? t("enable") : t("disable")}
        confirmButton={{
          isLoading: isLoading,
          colorScheme: selectedEmployee?.deletedAt ? "brand" : "red",
          loadingText: t("please wait"),
        }}
        isOpen={isOpen && !isWorkerColliding}
        onClose={() => (isWorkerColliding ? null : handleClose())}
        onConfirm={handleChangeStatus}
      >
        <Trans
          i18nKey={
            selectedEmployee?.deletedAt
              ? "worker enable alert body"
              : "worker disable alert body"
          }
          values={{ worker: selectedEmployee?.user?.fullname ?? "" }}
        />
      </AlertDialog>
      <WorkerCollisionModal
        startAt={schedule.startAt}
        endAt={schedule.endAt}
        scheduleId={schedule.id}
        isOpen={isWorkerColliding}
        submitButtonLabel={
          selectedEmployee?.deletedAt
            ? t("continue enable")
            : t("continue disable")
        }
        onSubmit={handleChangeStatus}
        onClose={handleClose}
      />
    </>
  );
};

export default ChangeStatusConfirmation;
