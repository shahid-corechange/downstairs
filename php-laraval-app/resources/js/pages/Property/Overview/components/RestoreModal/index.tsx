import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import Property from "@/types/property";

interface RestoreModalProps {
  data?: Property;
  isOpen: boolean;
  onClose: () => void;
  onSuccess?: () => void;
}

const RestoreModal = ({
  data,
  isOpen,
  onClose,
  onSuccess,
}: RestoreModalProps) => {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);

  const handleRestore = () => {
    setIsLoading(true);
    router.post(`/customers/properties/${data?.id}/restore`, undefined, {
      onFinish: () => setIsLoading(false),
      onSuccess: () => {
        onSuccess?.();
        onClose();
      },
    });
  };

  return (
    <AlertDialog
      title={t("restore property")}
      confirmButton={{
        isLoading,
        loadingText: t("please wait"),
      }}
      confirmText={t("restore")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleRestore}
    >
      <Trans
        i18nKey="property restore alert body"
        values={{ property: data?.address?.fullAddress ?? "" }}
      />
    </AlertDialog>
  );
};

export default RestoreModal;
