import { router } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import { ScheduleChangeRequest } from "@/types/schedule";

interface RejectModalProps {
  data?: ScheduleChangeRequest;
  isOpen: boolean;
  onClose: () => void;
}

const RejectModal = ({ data, isOpen, onClose }: RejectModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleReject = () => {
    setIsLoading(true);
    router.post(`/schedules/change-requests/${data?.id}/reject`, undefined, {
      onFinish: () => setIsLoading(false),
      onSuccess: onClose,
    });
  };

  return (
    <AlertDialog
      title={t("reject change request")}
      confirmButton={{
        isLoading,
        colorScheme: "red",
        loadingText: t("please wait"),
      }}
      confirmText={t("reject")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleReject}
    >
      {t("change request reject alert body")}
    </AlertDialog>
  );
};

export default RejectModal;
