import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import Product from "@/types/product";

interface DeleteModalProps {
  data?: Product;
  isOpen: boolean;
  onClose: () => void;
}

const DeleteModal = ({ data, isOpen, onClose }: DeleteModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleDelete = () => {
    setIsLoading(true);
    router.delete(`/products/${data?.id}`, {
      onFinish: () => setIsLoading(false),
      onSuccess: () => onClose(),
    });
  };

  return (
    <AlertDialog
      title={t("delete product")}
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
        i18nKey="product delete alert body"
        values={{ product: data?.name }}
      />
    </AlertDialog>
  );
};

export default DeleteModal;
