import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import ServiceQuarter from "@/types/serviceQuarter";

interface DeleteModalProps {
  data?: ServiceQuarter;
  isOpen: boolean;
  onClose: () => void;
}

const DeleteModal = ({ data, isOpen, onClose }: DeleteModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleDelete = () => {
    setIsLoading(true);
    router.delete(`/services/quarters/${data?.id}`, {
      onFinish: () => setIsLoading(false),
      onSuccess: onClose,
    });
  };

  return (
    <AlertDialog
      size="lg"
      title={t("delete service quarter")}
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
        i18nKey="service quarter delete alert body"
        values={{ service: data?.service?.name ?? "" }}
      />
    </AlertDialog>
  );
};

export default DeleteModal;
