import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import Property from "@/types/property";

interface DeleteModalProps {
  data?: Property;
  isOpen: boolean;
  onClose: () => void;
  onSuccess?: () => void;
}

const DeleteModal = ({
  data,
  isOpen,
  onClose,
  onSuccess,
}: DeleteModalProps) => {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);

  const handleDelete = () => {
    setIsLoading(true);
    router.delete(`/customers/properties/${data?.id}`, {
      onFinish: () => setIsLoading(false),
      onSuccess: () => {
        onSuccess?.();
        onClose();
      },
    });
  };

  return (
    <AlertDialog
      title={t("delete property")}
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
        i18nKey="property delete alert body"
        values={{ property: data?.address?.fullAddress ?? "" }}
      />
    </AlertDialog>
  );
};

export default DeleteModal;
