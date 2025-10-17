import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import { CartKey, RemoveFromCartProps } from "@/hooks/useCart";

import { CartProductModalData } from "@/types/cartProduct";

interface RemoveRutAlertProps {
  data?: CartProductModalData;
  isOpen: boolean;
  cartKey: CartKey;
  removeFromCart: (props: RemoveFromCartProps) => void;
  onClose: () => void;
}

const RemoveRutAlert = ({
  isOpen,
  data,
  cartKey,
  removeFromCart,
  onClose,
}: RemoveRutAlertProps) => {
  const { t } = useTranslation();

  const handleConfirm = () => {
    if (data?.index !== undefined) {
      removeFromCart({
        index: data.index,
        cartKey,
      });
    }
    onClose();
  };

  return (
    <AlertDialog
      title={t("remove product")}
      confirmButton={{
        colorScheme: "red",
      }}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleConfirm}
    >
      <Trans i18nKey="remove cart product alert body" />
    </AlertDialog>
  );
};

export default RemoveRutAlert;
