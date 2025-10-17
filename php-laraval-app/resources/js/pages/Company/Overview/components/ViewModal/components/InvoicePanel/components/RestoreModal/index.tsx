import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import Customer from "@/types/customer";

interface RestoreModalProps {
  companyId: number;
  isOpen: boolean;
  onClose: () => void;
  onRefetch: () => void;
  customer?: Customer;
}

const RestoreModal = ({
  companyId,
  customer,
  isOpen,
  onClose,
  onRefetch,
}: RestoreModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleRestore = () => {
    setIsLoading(true);

    router.post(
      `/companies/${companyId}/addresses/${customer?.id}/restore`,
      undefined,
      {
        onFinish: () => setIsLoading(false),
        onSuccess: () => {
          onClose();
          onRefetch();
        },
      },
    );
  };

  return (
    <AlertDialog
      title={t("restore address")}
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
        i18nKey="address restore alert body"
        values={{
          address: customer?.address?.fullAddress ?? "",
        }}
      />
    </AlertDialog>
  );
};

export default RestoreModal;
