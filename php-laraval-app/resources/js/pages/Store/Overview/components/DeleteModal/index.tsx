import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import { Store } from "@/types/store";

interface DeleteModalProps {
  data?: Store;
  isOpen: boolean;
  onClose: () => void;
}

const DeleteModal = ({ data, isOpen, onClose }: DeleteModalProps) => {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);

  const handleDelete = () => {
    setIsLoading(true);
    router.delete(`/stores/${data?.id}`, {
      onFinish: () => setIsLoading(false),
      onSuccess: () => onClose(),
    });
  };

  return (
    <AlertDialog
      title={t("delete store")}
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
      <Trans i18nKey="store delete alert body" values={{ store: data?.name }} />
    </AlertDialog>
  );
};

export default DeleteModal;
