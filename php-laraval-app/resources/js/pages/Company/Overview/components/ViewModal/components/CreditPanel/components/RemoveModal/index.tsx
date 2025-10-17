import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import Credit from "@/types/credit";

interface RemoveModalProps {
  data?: Credit;
  isOpen: boolean;
  onClose: () => void;
  onRefetch: () => void;
}

const RemoveModal = ({
  data,
  isOpen,
  onClose,
  onRefetch,
}: RemoveModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleRemove = () => {
    setIsLoading(true);
    router.delete(`/credits/${data?.id}`, {
      onFinish: () => setIsLoading(false),
      onSuccess: () => {
        onClose();
        onRefetch();
      },
    });
  };

  return (
    <AlertDialog
      title={t("remove credits")}
      confirmButton={{
        isLoading,
        colorScheme: "red",
        loadingText: t("please wait"),
      }}
      confirmText={t("remove")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleRemove}
    >
      <Trans
        i18nKey="credits remove alert body"
        values={{ amount: data?.remainingAmount }}
      />
    </AlertDialog>
  );
};

export default RemoveModal;
