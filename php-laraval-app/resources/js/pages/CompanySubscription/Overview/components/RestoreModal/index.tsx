import { router } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import Subscription from "@/types/subscription";

interface RestoreModalProps {
  data?: Subscription;
  isOpen: boolean;
  onClose: () => void;
}

const RestoreModal = ({ data, isOpen, onClose }: RestoreModalProps) => {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);

  const handleRestore = () => {
    setIsLoading(true);
    router.post(`/companies/subscriptions/${data?.id}/restore`, undefined, {
      onFinish: () => setIsLoading(false),
      onSuccess: onClose,
    });
  };

  return (
    <AlertDialog
      title={t("restore subscription")}
      confirmButton={{
        isLoading,
        loadingText: t("please wait"),
      }}
      confirmText={t("restore")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleRestore}
    >
      {t("subscription restore alert body")}
    </AlertDialog>
  );
};

export default RestoreModal;
