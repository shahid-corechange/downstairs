import { useState } from "react";
import { useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import { useRevertScheduleWorkerMutation } from "@/services/schedule";

import Deviation from "@/types/deviation";

export interface RevertModalProps {
  isOpen: boolean;
  onClose: () => void;
  deviation: Deviation;
}

const RevertModal = ({ deviation, isOpen, onClose }: RevertModalProps) => {
  const { t } = useTranslation();

  const revertWorkerMutation = useRevertScheduleWorkerMutation();

  const [isLoading, setIsLoading] = useState(false);

  const handleRevert = () => {
    if (!deviation.id || !deviation.user?.id) {
      return;
    }

    setIsLoading(true);

    revertWorkerMutation.mutate(
      {
        scheduleId: deviation.schedule?.id ?? 0,
        userId: deviation.user.id,
      },
      {
        onSettled: () => {
          setIsLoading(false);
        },
        onSuccess: onClose,
      },
    );
  };

  return (
    <AlertDialog
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
      {t("deviation revert alert body")}
    </AlertDialog>
  );
};

export default RevertModal;
