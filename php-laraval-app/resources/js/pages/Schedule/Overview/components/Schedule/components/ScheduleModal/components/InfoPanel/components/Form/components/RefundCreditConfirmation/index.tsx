import { useState } from "react";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import AlertDialog from "@/components/AlertDialog";

interface RefundCreditConfirmationProps {
  isOpen: boolean;
  refundCredit: number;
  handleSubmit: (onSettled?: () => void) => void;
  onClose: () => void;
}

const RefundCreditConfirmation = ({
  isOpen,
  refundCredit,
  handleSubmit,
  onClose,
}: RefundCreditConfirmationProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleRefundCredit = () => {
    setIsLoading(true);
    handleSubmit(() => setIsLoading(false));
  };

  return (
    <AlertDialog
      title={t("refund credit")}
      size="2xl"
      confirmButton={{
        tooltip: t("refund credit"),
        isLoading,
        colorScheme: "red",
        loadingText: t("please wait"),
      }}
      cancelText={t("close")}
      confirmText={t("refund credit")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleRefundCredit}
    >
      <Alert
        status="info"
        title={t("info")}
        message={t("refund credit alert info", {
          amount: refundCredit,
        })}
        fontSize="small"
        mb={6}
      />
      {t("refund credit alert body")}
    </AlertDialog>
  );
};

export default RefundCreditConfirmation;
