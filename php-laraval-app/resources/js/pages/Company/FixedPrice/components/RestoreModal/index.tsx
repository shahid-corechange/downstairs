import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import FixedPrice from "@/types/fixedPrice";

interface RestoreModalProps {
  data?: FixedPrice;
  isOpen: boolean;
  onClose: () => void;
}

const RestoreModal = ({ data, isOpen, onClose }: RestoreModalProps) => {
  const { t } = useTranslation();

  const [isLoading, setIsLoading] = useState(false);

  const handleRestore = () => {
    setIsLoading(true);
    router.post(`/companies/fixedprices/${data?.id}/restore`, undefined, {
      onFinish: () => setIsLoading(false),
      onSuccess: onClose,
    });
  };

  return (
    <AlertDialog
      size="lg"
      title={t("restore fixed price")}
      confirmButton={{
        isLoading,
        loadingText: t("please wait"),
      }}
      confirmText={t("restore")}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleRestore}
    >
      <Trans
        i18nKey="fixed price restore alert body"
        values={{ customer: data?.user?.fullname ?? "" }}
      />
    </AlertDialog>
  );
};

export default RestoreModal;
