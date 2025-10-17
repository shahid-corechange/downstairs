import { useState } from "react";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import AlertDialog from "@/components/AlertDialog";

import { useRevertScheduleWorkerMutation } from "@/services/schedule";

import { Response } from "@/types/api";
import Schedule from "@/types/schedule";
import ScheduleEmployee from "@/types/scheduleEmployee";

export interface RevertConfirmationProps {
  schedule: Schedule;
  selectedEmployee: ScheduleEmployee;
  isOpen: boolean;
  onClose: () => void;
  onSuccess: (data: Schedule, response: Response<Schedule>) => void;
}

const RevertConfirmation = ({
  schedule,
  selectedEmployee,
  isOpen,
  onClose,
  onSuccess,
}: RevertConfirmationProps) => {
  const { t } = useTranslation();

  const revertWorkerMutation = useRevertScheduleWorkerMutation();

  const [isLoading, setIsLoading] = useState(false);

  const handleRevert = () => {
    setIsLoading(true);

    revertWorkerMutation.mutate(
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
      size={schedule.status === "cancel" ? "2xl" : "xl"}
      title={t("revert")}
      confirmText={t("revert")}
      confirmButton={{
        isLoading,
        loadingText: t("please wait"),
      }}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleRevert}
    >
      {schedule.status === "cancel" && (
        <Alert
          status="info"
          title={t("info")}
          message={t("schedule worker revert booking alert info")}
          fontSize="small"
          mb={6}
        />
      )}
      {schedule.status === "cancel" && (
        <Alert
          status="info"
          title={t("info")}
          message={t("schedule worker revert credit alert info", {
            name: schedule?.user?.fullname,
          })}
          fontSize="small"
          mb={6}
        />
      )}
      {t("schedule worker revert alert body")}
    </AlertDialog>
  );
};

export default RevertConfirmation;
