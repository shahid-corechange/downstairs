import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import Product from "@/types/product";

interface RestoreModalProps {
  data?: Product;
  isOpen: boolean;
  onClose: () => void;
}

const RestoreModal = ({ data, isOpen, onClose }: RestoreModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleRestore = () => {
    setIsLoading(true);
    router.post(`/products/${data?.id}/restore`, undefined, {
      onFinish: () => setIsLoading(false),
      onSuccess: () => onClose(),
    });
  };

  return (
    <AlertDialog
      title={t("restore product")}
      confirmButton={{
        isLoading,
        loadingText: t("please wait"),
      }}
      confirmText={t("restore")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleRestore}
    >
      <Trans
        i18nKey="product restore alert body"
        values={{ product: data?.name }}
      />
    </AlertDialog>
  );
};

export default RestoreModal;
