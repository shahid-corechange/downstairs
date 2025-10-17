import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import { useRemoveScheduleWorkerMutation } from "@/services/schedule";

import { Response } from "@/types/api";
import Schedule from "@/types/schedule";
import ScheduleEmployee from "@/types/scheduleEmployee";

interface RemoveConfirmationProps {
  schedule: Schedule;
  selectedEmployee: ScheduleEmployee;
  isOpen: boolean;
  onClose: () => void;
  onSuccess: (data: Schedule, response: Response<Schedule>) => void;
}

const RemoveConfirmation = ({
  schedule,
  selectedEmployee,
  isOpen,
  onClose,
  onSuccess,
}: RemoveConfirmationProps) => {
  const { t } = useTranslation();

  const removeWorkerMutation = useRemoveScheduleWorkerMutation();

  const [isLoading, setIsLoading] = useState(false);

  const handleRemove = () => {
    setIsLoading(true);

    removeWorkerMutation.mutate(
      {
        scheduleId: schedule.id,
        userId: selectedEmployee?.userId,
      },
      {
        onSettled: () => {
          setIsLoading(false);
        },
        onSuccess: ({ data, response }) => {
          onClose();
          onSuccess(data, response);
        },
      },
    );
  };

  return (
    <AlertDialog
      title={t("remove worker")}
      confirmText={t("remove")}
      confirmButton={{
        isLoading: isLoading,
        colorScheme: "red",
        loadingText: t("please wait"),
      }}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleRemove}
    >
      <Trans
        i18nKey={"worker remove alert body"}
        values={{ worker: selectedEmployee?.user?.fullname ?? "" }}
      />
    </AlertDialog>
  );
};

export default RemoveConfirmation;
