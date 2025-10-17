import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import FixedPrice from "@/types/fixedPrice";
import User from "@/types/user";

interface DeleteModalProps {
  user: User;
  isOpen: boolean;
  onRefetch: () => void;
  onClose: () => void;
  data?: FixedPrice;
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
    router.delete(`/companies/fixedprices/${data?.id}`, {
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
      title={t("delete fixed price")}
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
        i18nKey="fixed price delete alert body"
        values={{ customer: user.fullname }}
      />
    </AlertDialog>
  );
};

export default DeleteModal;
