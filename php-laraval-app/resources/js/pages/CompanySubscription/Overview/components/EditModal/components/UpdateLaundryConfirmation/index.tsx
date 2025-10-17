import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import AlertDialog from "@/components/AlertDialog";

interface UpdateLaundryConfirmationProps {
  isOpen: boolean;
  isLoading: boolean;
  onClose: () => void;
  onConfirm: () => void;
}

const UpdateLaundryConfirmation = ({
  isOpen,
  isLoading,
  onClose,
  onConfirm,
}: UpdateLaundryConfirmationProps) => {
  const { t } = useTranslation();

  const handleConfirm = () => {
    onConfirm();
  };

  return (
    <AlertDialog
      title={t("update subscription")}
      confirmButton={{
        isLoading,
        colorScheme: "red",
        loadingText: t("please wait"),
      }}
      confirmText={t("update")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleConfirm}
      size="xl"
    >
      <Alert
        status="warning"
        title={t("warning")}
        message={t("subscription update laundry alert warning")}
        fontSize="small"
        mb={6}
      />

      {t("subscription update laundry alert body")}
    </AlertDialog>
  );
};

export default UpdateLaundryConfirmation;
