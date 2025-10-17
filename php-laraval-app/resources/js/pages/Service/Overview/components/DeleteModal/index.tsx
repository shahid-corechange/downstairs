import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import Service from "@/types/service";

interface DeleteModalProps {
  data?: Service;
  isOpen: boolean;
  onClose: () => void;
}

const DeleteModal = ({ data, isOpen, onClose }: DeleteModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleDelete = () => {
    setIsLoading(true);
    router.delete(`/services/${data?.id}`, {
      onFinish: () => setIsLoading(false),
      onSuccess: onClose,
    });
  };

  return (
    <AlertDialog
      title={t("delete service")}
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
      <Trans
        i18nKey="service delete alert body"
        values={{ service: data?.name }}
      />
    </AlertDialog>
  );
};

export default DeleteModal;
