import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";

import { CartKey, UpdateCartProductProps } from "@/hooks/useCart";

import { CartProduct, CartProductModalData } from "@/types/cartProduct";
import Product from "@/types/product";

interface RemoveRutAlertProps {
  data?: CartProductModalData;
  isOpen: boolean;
  cartProducts: CartProduct[];
  products: Product[];
  cartKey: CartKey;
  updateCart: (props: UpdateCartProductProps) => void;
  onClose: () => void;
}

const RemoveRutAlert = ({
  isOpen,
  data,
  cartProducts,
  products,
  cartKey,
  updateCart,
  onClose,
}: RemoveRutAlertProps) => {
  const { t } = useTranslation();

  const handleConfirm = () => {
    if (data?.index !== undefined) {
      const oldProduct = products.find(
        (product) => product.id === cartProducts[data.index].id,
      );

      updateCart({
        index: data.index,
        updates: {
          hasRut: false,
        },
        oldProduct,
        cartKey,
      });
    }
    onClose();
  };

  return (
    <AlertDialog
      title={t("remove rut")}
      confirmButton={{
        colorScheme: "red",
      }}
      isOpen={isOpen}
      onClose={onClose}
      onConfirm={handleConfirm}
    >
      <Trans i18nKey="remove rut alert body" />
    </AlertDialog>
  );
};

export default RemoveRutAlert;
