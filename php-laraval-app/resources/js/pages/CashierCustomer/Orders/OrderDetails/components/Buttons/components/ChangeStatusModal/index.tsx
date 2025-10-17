import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import { LaundryOrder } from "@/types/laundryOrder";

interface ChangeStatusModalProps {
  data?: LaundryOrder;
  isOpen: boolean;
  onClose: () => void;
}

const ChangeStatusModal = ({
  data,
  isOpen,
  onClose,
}: ChangeStatusModalProps) => {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = () => {
    const status = data?.status === "paid" ? "closed" : "done";
    const payload = {
      status,
      ...(status === "done" && {
        message: t("message done laundry order", { code: data?.id }),
        sendMessage: true,
      }),
    };

    setIsLoading(true);

    router.post(`/cashier/orders/${data?.id}/change-status`, payload, {
      onFinish: () => setIsLoading(false),
      onSuccess: onClose,
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
        values={{ status: t("done") }}
      />
    </AlertDialog>
  );
};

export default ChangeStatusModal;
