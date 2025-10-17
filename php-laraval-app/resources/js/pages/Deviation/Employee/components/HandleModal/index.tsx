import { router } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import Deviation from "@/types/deviation";

export interface HandleModalProps {
  isOpen: boolean;
  onClose: () => void;
  data?: Deviation;
}

const HandleModal = ({ data, onClose, isOpen }: HandleModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = () => {
    setIsLoading(true);
    router.post(`/deviations/employee/${data?.id}/handle`, undefined, {
      onFinish: () => setIsLoading(false),
      onSuccess: onClose,
    });
  };

  return (
    <AlertDialog
      title={t("handle")}
      confirmButton={{
        isLoading,
        loadingText: t("please wait"),
      }}
      confirmText={t("handle")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleSubmit}
    >
      {t("deviation handle alert body")}
    </AlertDialog>
  );
};

export default HandleModal;
