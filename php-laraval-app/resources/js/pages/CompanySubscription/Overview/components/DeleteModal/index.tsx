import { router } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import AlertDialog from "@/components/AlertDialog";

import Subscription from "@/types/subscription";

interface DeleteModalProps {
  data?: Subscription;
  isOpen: boolean;
  onClose: () => void;
}

const DeleteModal = ({ data, isOpen, onClose }: DeleteModalProps) => {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);

  const handleDelete = () => {
    setIsLoading(true);
    router.delete(`/companies/subscriptions/${data?.id}`, {
      onFinish: () => setIsLoading(false),
      onSuccess: onClose,
    });
  };

  return (
    <AlertDialog
      title={t("delete subscription")}
      confirmButton={{
        isLoading,
        colorScheme: "red",
        loadingText: t("please wait"),
      }}
      confirmText={t("delete")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleDelete}
      size="xl"
    >
      {data?.isCleaningHasLaundry && (
        <Alert
          status="warning"
          title={t("warning")}
          message={t("subscription delete laundry alert warning")}
          fontSize="small"
          mb={6}
        />
      )}

      {t("subscription delete alert body")}
    </AlertDialog>
  );
};

export default DeleteModal;
