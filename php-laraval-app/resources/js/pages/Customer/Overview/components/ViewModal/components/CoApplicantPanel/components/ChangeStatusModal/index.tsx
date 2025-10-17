import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import { RutCoApplicant } from "@/types/rutCoApplicant";

interface ChangeStatusModalProps {
  userId: number;
  isOpen: boolean;
  onClose: () => void;
  onRefetch: () => void;
  rutCoApplicant?: RutCoApplicant;
}

const ChangeStatusModal = ({
  userId,
  rutCoApplicant,
  isOpen,
  onClose,
  onRefetch,
}: ChangeStatusModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);
  const action = rutCoApplicant?.isEnabled ? "disable" : "enable";

  const handleChangeStatus = () => {
    setIsLoading(true);

    router.post(
      `/customers/${userId}/rut-co-applicants/${rutCoApplicant?.id}/${action}`,
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
      title={t(`${action} rut co applicant`)}
      confirmButton={{
        isLoading,
        colorScheme: action === "enable" ? "brand" : "red",
        loadingText: t("please wait"),
      }}
      confirmText={t(action)}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleChangeStatus}
    >
      <Trans
        i18nKey={`rut co applicant ${action} alert body`}
        values={{
          name: rutCoApplicant?.name,
        }}
      />
    </AlertDialog>
  );
};

export default ChangeStatusModal;
