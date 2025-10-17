import { Button, Flex, Icon, Text } from "@chakra-ui/react";
import { useTranslation } from "react-i18next";
import { MdDeleteOutline, MdOutlineShoppingCartCheckout } from "react-icons/md";

import { CartProduct } from "@/types/cartProduct";

interface ButtonsProps {
  cartProducts: CartProduct[];
  openCartProductModal: (modal: "emptyCart") => void;
  handleGoToCheckout: () => void;
}

const Buttons = ({
  cartProducts,
  openCartProductModal,
  handleGoToCheckout,
}: ButtonsProps) => {
  const { t } = useTranslation();

  return (
    <Flex justify="flex-end" w="full" gap={4}>
      {cartProducts.length > 0 && (
        <Button
          variant="solid"
          colorScheme="gray"
          aria-label={t("empty cart")}
          onClick={() => openCartProductModal("emptyCart")}
          flexDirection="column"
          alignItems="center"
          justifyContent="center"
          gap={2}
          p={2}
          h={24}
          w={24}
        >
          <Icon as={MdDeleteOutline} boxSize={8} />
          <Text
            whiteSpace="pre-wrap"
            wordBreak="break-word"
            fontSize="sm"
            lineHeight="short"
            textAlign="center"
          >
            {t("empty cart")}
          </Text>
        </Button>
      )}
      <Button
        variant="solid"
        colorScheme="brand"
        aria-label={t("go to checkout")}
        onClick={() => handleGoToCheckout()}
        flexDirection="column"
        alignItems="center"
        justifyContent="center"
        gap={2}
        p={2}
        h={24}
        w={24}
        isDisabled={cartProducts.length === 0}
      >
        <Icon as={MdOutlineShoppingCartCheckout} boxSize={8} />
        <Text
          whiteSpace="pre-wrap"
          wordBreak="break-word"
          fontSize="sm"
          lineHeight="short"
          textAlign="center"
        >
          {t("go to checkout")}
        </Text>
      </Button>
    </Flex>
  );
};

export default Buttons;
