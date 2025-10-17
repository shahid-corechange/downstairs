import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import { RutCoApplicant } from "@/types/rutCoApplicant";

interface DeleteModalProps {
  userId: number;
  isOpen: boolean;
  onClose: () => void;
  onRefetch: () => void;
  rutCoApplicant?: RutCoApplicant;
}

const DeleteModal = ({
  userId,
  rutCoApplicant,
  isOpen,
  onClose,
  onRefetch,
}: DeleteModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleDelete = () => {
    setIsLoading(true);

    router.delete(
      `/customers/${userId}/rut-co-applicants/${rutCoApplicant?.id}`,
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
      title={t("delete rut co applicant")}
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
        i18nKey="rut co applicant delete alert body"
        values={{
          name: rutCoApplicant?.name,
        }}
      />
    </AlertDialog>
  );
};

export default DeleteModal;
