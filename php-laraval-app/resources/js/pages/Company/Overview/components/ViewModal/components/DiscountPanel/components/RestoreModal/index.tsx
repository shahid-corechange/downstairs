import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import CustomerDiscount from "@/types/customerDiscount";
import User from "@/types/user";

interface RestoreModalProps {
  user: User;
  isOpen: boolean;
  onRefetch: () => void;
  onClose: () => void;
  data?: CustomerDiscount;
}

const RestoreModal = ({
  user,
  data,
  isOpen,
  onRefetch,
  onClose,
}: RestoreModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleRestore = () => {
    setIsLoading(true);
    router.post(`/companies/discounts/${data?.id}/restore`, undefined, {
      onFinish: () => setIsLoading(false),
      onSuccess: () => {
        onRefetch();
        onClose();
      },
    });
  };

  return (
    <AlertDialog
      size="lg"
      title={t("restore company discount")}
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
        i18nKey="company discount restore alert body"
        values={{ customer: user.fullname }}
      />
    </AlertDialog>
  );
};

export default RestoreModal;
