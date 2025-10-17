import { Button, Flex, Icon, Text } from "@chakra-ui/react";
import { router } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { AiOutlineArrowLeft, AiOutlinePlus } from "react-icons/ai";

interface ButtonsProps {
  customerId: number;
  isSubmitting: boolean;
}

const Buttons = ({ customerId, isSubmitting }: ButtonsProps) => {
  const { t } = useTranslation();

  const handleGoToCart = () => {
    router.get(`/cashier/customers/${customerId}/cart`);
  };

  return (
    <Flex justify="flex-end" w="full" gap={4}>
      <Button
        variant="solid"
        colorScheme="gray"
        aria-label={t("back to cart")}
        onClick={() => handleGoToCart()}
        flexDirection="column"
        alignItems="center"
        justifyContent="center"
        gap={2}
        p={2}
        h={24}
        w={24}
      >
        <Icon as={AiOutlineArrowLeft} boxSize={8} />
        <Text
          whiteSpace="pre-wrap"
          wordBreak="break-word"
          fontSize="sm"
          lineHeight="short"
          textAlign="center"
        >
          {t("back to cart")}
        </Text>
      </Button>

      <Button
        type="submit"
        variant="solid"
        colorScheme="brand"
        aria-label={t("create order")}
        flexDirection="column"
        alignItems="center"
        justifyContent="center"
        gap={2}
        p={2}
        h={24}
        w={24}
        isLoading={isSubmitting}
      >
        <Icon as={AiOutlinePlus} boxSize={8} />
        <Text
          whiteSpace="pre-wrap"
          wordBreak="break-word"
          fontSize="sm"
          lineHeight="short"
          textAlign="center"
        >
          {t("create order")}
        </Text>
      </Button>
    </Flex>
  );
};

export default Buttons;
