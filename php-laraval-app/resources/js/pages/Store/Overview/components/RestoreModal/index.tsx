import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import { Store } from "@/types/store";

interface RestoreModalProps {
  data?: Store;
  isOpen: boolean;
  onClose: () => void;
}

const RestoreModal = ({ data, isOpen, onClose }: RestoreModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleRestore = () => {
    setIsLoading(true);
    router.post(`/stores/${data?.id}/restore`, undefined, {
      onFinish: () => setIsLoading(false),
      onSuccess: () => onClose(),
    });
  };

  return (
    <AlertDialog
      title={t("restore store")}
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
        i18nKey="store restore alert body"
        values={{ store: data?.name }}
      />
    </AlertDialog>
  );
};

export default RestoreModal;
