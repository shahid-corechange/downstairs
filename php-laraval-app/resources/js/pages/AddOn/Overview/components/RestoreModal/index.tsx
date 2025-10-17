import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import Addon from "@/types/addon";

interface RestoreModalProps {
  data?: Addon;
  isOpen: boolean;
  onClose: () => void;
}

const RestoreModal = ({ data, isOpen, onClose }: RestoreModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleRestore = () => {
    setIsLoading(true);
    router.post(`/addons/${data?.id}/restore`, undefined, {
      onFinish: () => setIsLoading(false),
      onSuccess: onClose,
    });
  };

  return (
    <AlertDialog
      title={t("restore add on")}
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
        i18nKey="add on restore alert body"
        values={{ addon: data?.name }}
      />
    </AlertDialog>
  );
};

export default RestoreModal;
