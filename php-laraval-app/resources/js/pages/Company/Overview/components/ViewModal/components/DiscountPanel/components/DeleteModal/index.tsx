import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import CustomerDiscount from "@/types/customerDiscount";
import User from "@/types/user";

interface DeleteModalProps {
  user: User;
  isOpen: boolean;
  onRefetch: () => void;
  onClose: () => void;
  data?: CustomerDiscount;
}

const DeleteModal = ({
  user,
  data,
  isOpen,
  onRefetch,
  onClose,
}: DeleteModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleDelete = () => {
    setIsLoading(true);
    router.delete(`/companies/discounts/${data?.id}`, {
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
      title={t("delete company discount")}
      confirmButton={{
        isLoading,
        colorScheme: "red",
        loadingText: t("please wait"),
      }}
      confirmText={t("delete")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleDelete}
    >
      <Trans
        i18nKey="company discount delete alert body"
        values={{ customer: user.fullname }}
      />
    </AlertDialog>
  );
};

export default DeleteModal;
