import { router } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import LeaveRegistration from "@/types/leaveRegistration";

interface StopModalProps {
  data?: LeaveRegistration;
  isOpen: boolean;
  onClose: () => void;
}

const StopModal = ({ data, isOpen, onClose }: StopModalProps) => {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);

  const handleStop = () => {
    setIsLoading(true);
    router.post(`/leave-registrations/${data?.id}/stop`, undefined, {
      onFinish: () => setIsLoading(false),
      onSuccess: onClose,
    });
  };

  return (
    <AlertDialog
      title={t("stop leave registration")}
      confirmButton={{
        isLoading,
        colorScheme: "red",
        loadingText: t("please wait"),
      }}
      confirmText={t("stop")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleStop}
    >
      {t("leave registration stop alert body")}
    </AlertDialog>
  );
};

export default StopModal;
