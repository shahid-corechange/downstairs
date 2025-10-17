import { router } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import AlertDialog from "@/components/AlertDialog";

import Subscription from "@/types/subscription";

interface PauseModalProps {
  data?: Subscription;
  isOpen: boolean;
  onClose: () => void;
}

const PauseModal = ({ data, isOpen, onClose }: PauseModalProps) => {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);

  const handlePause = () => {
    setIsLoading(true);
    router.post(`/customers/subscriptions/${data?.id}/pause`, undefined, {
      onFinish: () => setIsLoading(false),
      onSuccess: onClose,
    });
  };

  return (
    <AlertDialog
      title={t("pause subscription")}
      confirmButton={{
        isLoading,
        colorScheme: "red",
        loadingText: t("please wait"),
      }}
      confirmText={t("pause")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handlePause}
      size="xl"
    >
      {data?.isCleaningHasLaundry && (
        <Alert
          status="warning"
          title={t("warning")}
          message={t("subscription pause laundry alert warning")}
          fontSize="small"
          mb={6}
        />
      )}

      {t("subscription pause alert body")}
    </AlertDialog>
  );
};

export default PauseModal;
