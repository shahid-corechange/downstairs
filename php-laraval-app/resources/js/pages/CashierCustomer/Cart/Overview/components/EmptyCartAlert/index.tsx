import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import { CartKey } from "@/hooks/useCart";

interface EmptyCartAlertProps {
  isOpen: boolean;
  cartKey: CartKey;
  clearCart: (props: CartKey) => void;
  onClose: () => void;
}

const EmptyCartAlert = ({
  isOpen,
  cartKey,
  clearCart,
  onClose,
}: EmptyCartAlertProps) => {
  const { t } = useTranslation();

  const handleConfirm = () => {
    clearCart(cartKey);
    onClose();
  };

  return (
    <AlertDialog
      title={t("empty cart")}
      confirmButton={{
        colorScheme: "red",
      }}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleConfirm}
    >
      <Trans i18nKey="empty cart alert body" />
    </AlertDialog>
  );
};

export default EmptyCartAlert;
