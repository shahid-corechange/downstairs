import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import { DATE_FORMAT } from "@/constants/datetime";

import { RutCoApplicant } from "@/types/rutCoApplicant";

import { toDayjs } from "@/utils/datetime";

interface PauseNowModalProps {
  userId: number;
  isOpen: boolean;
  onClose: () => void;
  onRefetch: () => void;
  rutCoApplicant?: RutCoApplicant;
}

const PauseNowModal = ({
  userId,
  rutCoApplicant,
  isOpen,
  onClose,
  onRefetch,
}: PauseNowModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleDelete = () => {
    setIsLoading(true);

    router.post(
      `/customers/${userId}/rut-co-applicants/${rutCoApplicant?.id}/pause`,
      {
        pauseStartDate: toDayjs().startOf("month").format(DATE_FORMAT),
      },
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
      title={t("pause rut co applicant")}
      confirmButton={{
        isLoading,
        colorScheme: "red",
        loadingText: t("please wait"),
      }}
      confirmText={t("pause now")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleDelete}
    >
      <Trans
        i18nKey="rut co applicant pause alert body"
        values={{
          name: rutCoApplicant?.name,
        }}
      />
    </AlertDialog>
  );
};

export default PauseNowModal;
