import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import { TIME_FORMAT } from "@/constants/datetime";

import { BlockDay } from "@/types/blockday";

import { toDayjs } from "@/utils/datetime";

export interface HandleModalProps {
  isOpen: boolean;
  onClose: () => void;
  data?: BlockDay | string;
}

const HandleModal = ({ data, onClose, isOpen }: HandleModalProps) => {
  const { t } = useTranslation();

  const isBlockedDay = typeof data === "object";

  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = () => {
    if (isBlockedDay) {
      router.delete(`/blockdays/${data.id}`, {
        onFinish: () => setIsLoading(false),
        onSuccess: onClose,
      });
    } else {
      router.post(
        `/blockdays`,
        {
          blockDate: data,
          startBlockTime: toDayjs(data).startOf("day").format(TIME_FORMAT),
          endBlockTime: toDayjs(data).endOf("day").format(TIME_FORMAT),
        },
        {
          onFinish: () => setIsLoading(false),
          onSuccess: onClose,
        },
      );
    }
  };

  return (
    <AlertDialog
      title={isBlockedDay ? t("remove block day") : t("add block day")}
      confirmButton={{
        isLoading,
        loadingText: t("please wait"),
      }}
      confirmText={isBlockedDay ? t("remove") : t("add")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleSubmit}
    >
      <Trans
        i18nKey="block day alert body"
        values={{ action: isBlockedDay ? t("remove") : t("add") }}
      />
    </AlertDialog>
  );
};

export default HandleModal;
