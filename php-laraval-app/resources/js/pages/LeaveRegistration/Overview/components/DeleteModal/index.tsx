import { router } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import LeaveRegistration from "@/types/leaveRegistration";

interface DeleteModalProps {
  data?: LeaveRegistration;
  isOpen: boolean;
  onClose: () => void;
}

const DeleteModal = ({ data, isOpen, onClose }: DeleteModalProps) => {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);

  const handleDelete = () => {
    setIsLoading(true);
    router.delete(`/leave-registrations/${data?.id}`, {
      onFinish: () => setIsLoading(false),
      onSuccess: onClose,
    });
  };

  return (
    <AlertDialog
      title={t("delete leave registration")}
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
      {t("leave registration delete alert body")}
    </AlertDialog>
  );
};

export default DeleteModal;
