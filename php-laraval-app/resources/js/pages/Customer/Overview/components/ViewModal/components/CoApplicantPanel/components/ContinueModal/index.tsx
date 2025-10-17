import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import { RutCoApplicant } from "@/types/rutCoApplicant";

interface ContinueModalProps {
  userId: number;
  isOpen: boolean;
  onClose: () => void;
  onRefetch: () => void;
  rutCoApplicant?: RutCoApplicant;
}

const ContinueModal = ({
  userId,
  rutCoApplicant,
  isOpen,
  onClose,
  onRefetch,
}: ContinueModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleContinue = () => {
    setIsLoading(true);

    router.post(
      `/customers/${userId}/rut-co-applicants/${rutCoApplicant?.id}/continue`,
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
      title={t("continue rut co applicant")}
      confirmButton={{
        isLoading,
        colorScheme: "brand",
        loadingText: t("please wait"),
      }}
      confirmText={t("continue")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleContinue}
    >
      <Trans
        i18nKey="rut co applicant continue alert body"
        values={{
          name: rutCoApplicant?.name,
        }}
      />
    </AlertDialog>
  );
};

export default ContinueModal;
