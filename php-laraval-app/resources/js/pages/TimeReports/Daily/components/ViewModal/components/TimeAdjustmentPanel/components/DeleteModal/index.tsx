import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

interface DeleteModalProps {
  timeAdjustmentId?: number;
  isOpen: boolean;
  onClose: () => void;
  onRefetch: () => void;
}

const DeleteModal = ({
  timeAdjustmentId,
  isOpen,
  onClose,
  onRefetch,
}: DeleteModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleDelete = () => {
    setIsLoading(true);

    router.delete(`/time-adjustments/${timeAdjustmentId}`, {
      onFinish: () => setIsLoading(false),
      onSuccess: () => {
        onClose();
        onRefetch();
      },
    });
  };

  return (
    <AlertDialog
      title={t("delete time adjustment")}
      confirmButton={{
        isLoading,
        colorScheme: "red",
        loadingText: t("please wait"),
      }}
      confirmText={t("delete")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleDelete}
    >
      <Trans i18nKey="time adjustment delete alert body" />
    </AlertDialog>
  );
};

export default DeleteModal;
