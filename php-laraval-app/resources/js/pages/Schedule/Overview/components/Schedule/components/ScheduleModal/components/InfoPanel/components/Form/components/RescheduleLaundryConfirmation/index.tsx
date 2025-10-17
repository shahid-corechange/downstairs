import { useState } from "react";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import AlertDialog from "@/components/AlertDialog";

interface RescheduleLaundryConfirmationProps {
  isOpen: boolean;
  type?: string;
  handleSubmit: (onSettled?: () => void) => void;
  onClose: () => void;
}

const RescheduleLaundryConfirmation = ({
  isOpen,
  type,
  handleSubmit,
  onClose,
}: RescheduleLaundryConfirmationProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleRefundCredit = () => {
    setIsLoading(true);
    handleSubmit(() => setIsLoading(false));
  };

  return (
    <AlertDialog
      title={t("reschedule laundry schedule", {
        type: t(type ?? ""),
      })}
      size="lg"
      confirmButton={{
        isLoading,
        colorScheme: "red",
        loadingText: t("please wait"),
      }}
      cancelText={t("close")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleRefundCredit}
    >
      <Alert
        status="warning"
        title={t("warning")}
        message={t("reschedule laundry schedule alert warning", {
          type: type,
        })}
        fontSize="small"
        mb={6}
      />
      {t("reschedule laundry schedule alert body")}
    </AlertDialog>
  );
};

export default RescheduleLaundryConfirmation;
