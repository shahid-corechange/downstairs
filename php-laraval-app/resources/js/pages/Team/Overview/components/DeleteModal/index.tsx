import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import Team from "@/types/team";

interface DeleteModalProps {
  data?: Team;
  isOpen: boolean;
  onClose: () => void;
}

const DeleteModal = ({ data, isOpen, onClose }: DeleteModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleDelete = () => {
    setIsLoading(true);
    router.delete(`/teams/${data?.id}`, {
      onFinish: () => setIsLoading(false),
      onSuccess: onClose,
    });
  };

  return (
    <AlertDialog
      title={t("delete team")}
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
      <Trans i18nKey="team delete alert body" values={{ team: data?.name }} />
    </AlertDialog>
  );
};

export default DeleteModal;
