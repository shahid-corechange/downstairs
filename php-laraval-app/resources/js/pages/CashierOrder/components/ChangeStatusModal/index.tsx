import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import { LaundryOrder } from "@/types/laundryOrder";

import { getNextStatus } from "@/utils/laundryOrder";

interface ChangeStatusModalProps {
  data?: LaundryOrder;
  isOpen: boolean;
  onClose: () => void;
  onRefetch: () => void;
}

const ChangeStatusModal = ({
  data,
  isOpen,
  onClose,
  onRefetch,
}: ChangeStatusModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = () => {
    if (!data) {
      return;
    }

    setIsLoading(true);

    const nextStatus = getNextStatus(data.status);
    const payload = {
      status: nextStatus,
      ...(nextStatus === "done" && {
        message: t("message done laundry order", { code: data?.id }),
        sendMessage: true,
      }),
    };

    router.post(`/cashier/orders/${data?.id}/change-status`, payload, {
      onFinish: () => setIsLoading(false),
      onSuccess: () => {
        onClose();
        onRefetch();
      },
    });
  };

  return (
    <AlertDialog
      title={t("change status")}
      confirmButton={{
        isLoading,
        loadingText: t("please wait"),
      }}
      confirmText={t("change status")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleSubmit}
    >
      <Trans
        i18nKey="cashier order change status alert body"
        values={{ status: t(getNextStatus(data?.status ?? "")) }}
      />
    </AlertDialog>
  );
};

export default ChangeStatusModal;
