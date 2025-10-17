import { router } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import { ScheduleChangeRequest } from "@/types/schedule";

interface ApproveModalProps {
  data?: ScheduleChangeRequest;
  isOpen: boolean;
  onClose: () => void;
}

const ApproveModal = ({ data, isOpen, onClose }: ApproveModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleApprove = () => {
    setIsLoading(true);
    router.post(`/schedules/change-requests/${data?.id}/approve`, undefined, {
      onFinish: () => setIsLoading(false),
      onSuccess: onClose,
    });
  };

  return (
    <AlertDialog
      title={t("approve change request")}
      confirmButton={{
        isLoading,
        loadingText: t("please wait"),
      }}
      confirmText={t("approve")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleApprove}
    >
      {t("change request approve alert body")}
    </AlertDialog>
  );
};

export default ApproveModal;
