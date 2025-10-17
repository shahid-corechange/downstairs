import { Button, Flex, Icon, Text } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useRef } from "react";
import { useTranslation } from "react-i18next";
import {
  AiOutlineCreditCard,
  AiOutlineFileText,
  AiOutlineSave,
} from "react-icons/ai";
import { MdOutlineDone, MdOutlineShoppingCart } from "react-icons/md";

import { PaymentMethod } from "@/constants/paymentMethod";

import { usePageModal } from "@/hooks/modal";

import { LaundryOrder } from "@/types/laundryOrder";

import { compareLaundryOrderStatus } from "@/utils/laundryOrder";

import { PageProps } from "@/types";

import ChangeStatusModal from "./components/ChangeStatusModal";
import ReceiptModal from "./components/ReceiptModal";

interface ButtonsProps {
  laundryOrder?: LaundryOrder;
  isSubmitting: boolean;
}

const Buttons = ({ laundryOrder, isSubmitting }: ButtonsProps) => {
  const { t } = useTranslation();
  const { flash } = usePage<PageProps>().props;
  const hasAutoOpened = useRef(false);

  const { modalData, modal, openModal, closeModal } = usePageModal<
    LaundryOrder,
    "receipt" | "changeStatus" | "payment"
  >();

  // Auto-open receipt modal when order is created successfully
  useEffect(() => {
    if (
      !hasAutoOpened.current &&
      flash.success === t("laundry order created successfully") &&
      laundryOrder
    ) {
      hasAutoOpened.current = true;
      openModal("receipt", laundryOrder);
    }
  }, [flash.success, laundryOrder, openModal, t]);

  const handleGoToCart = () => {
    router.get(`/cashier/customers/${laundryOrder?.user?.id}/cart`, {
      laundryOrderId: laundryOrder?.id,
    });
  };

  const handleCardPayment = () => {
    router.get(
      `/cashier/customers/${laundryOrder?.user?.id}/orders/${laundryOrder?.id}/card-payment`,
    );
  };

  const handleInvoicePayment = () => {
    router.get(
      `/cashier/customers/${laundryOrder?.user?.id}/orders/${laundryOrder?.id}/invoice-payment`,
    );
  };

  const isPayWithInvoice =
    laundryOrder?.paymentMethod === PaymentMethod.INVOICE;

  return (
    <>
      <Flex justify="flex-end" w="full" gap={4}>
        {compareLaundryOrderStatus("before", "paid", laundryOrder?.status) && (
          <>
            <Button
              type="submit"
              variant="solid"
              colorScheme="brand"
              aria-label={t("save and close")}
              flexDirection="column"
              alignItems="center"
              justifyContent="center"
              gap={2}
              p={2}
              h={24}
              w={24}
              isLoading={isSubmitting}
            >
              <Icon as={AiOutlineSave} boxSize={8} />
              <Text
                whiteSpace="pre-wrap"
                wordBreak="break-word"
                fontSize="sm"
                lineHeight="short"
                textAlign="center"
              >
                {t("save and close")}
              </Text>
            </Button>

            <Button
              variant="solid"
              colorScheme="brand"
              aria-label={t("add product")}
              onClick={() => handleGoToCart()}
              flexDirection="column"
              alignItems="center"
              justifyContent="center"
              gap={2}
              p={2}
              h={24}
              w={24}
              isLoading={isSubmitting}
            >
              <Icon as={MdOutlineShoppingCart} boxSize={8} />
              <Text
                whiteSpace="pre-wrap"
                wordBreak="break-word"
                fontSize="sm"
                lineHeight="short"
                textAlign="center"
              >
                {t("add product")}
              </Text>
            </Button>
          </>
        )}

        {compareLaundryOrderStatus("before", "done", laundryOrder?.status) && (
          <Button
            variant="solid"
            colorScheme="brand"
            aria-label={t("mark as done")}
            onClick={() => openModal("changeStatus", laundryOrder)}
            flexDirection="column"
            alignItems="center"
            justifyContent="center"
            gap={2}
            p={2}
            h={24}
            w={24}
            isLoading={isSubmitting}
          >
            <Icon as={MdOutlineDone} boxSize={8} />
            <Text
              whiteSpace="pre-wrap"
              wordBreak="break-word"
              fontSize="sm"
              lineHeight="short"
              textAlign="center"
            >
              {t("mark as done")}
            </Text>
          </Button>
        )}

        {compareLaundryOrderStatus("current", "paid", laundryOrder?.status) && (
          <Button
            variant="solid"
            colorScheme="brand"
            aria-label={t("mark as closed")}
            onClick={() => openModal("changeStatus", laundryOrder)}
            flexDirection="column"
            alignItems="center"
            justifyContent="center"
            gap={2}
            p={2}
            h={24}
            w={24}
            isLoading={isSubmitting}
          >
            <Icon as={MdOutlineDone} boxSize={8} />
            <Text
              whiteSpace="pre-wrap"
              wordBreak="break-word"
              fontSize="sm"
              lineHeight="short"
              textAlign="center"
            >
              {t("mark as closed")}
            </Text>
          </Button>
        )}

        {compareLaundryOrderStatus("current", "done", laundryOrder?.status) && (
          <>
            {!isPayWithInvoice && (
              <Button
                variant="solid"
                colorScheme="yellow"
                aria-label={t("pay with card")}
                onClick={() => handleCardPayment()}
                flexDirection="column"
                alignItems="center"
                justifyContent="center"
                gap={2}
                p={2}
                h={24}
                w={24}
                isLoading={isSubmitting}
              >
                <Icon as={AiOutlineCreditCard} boxSize={8} />
                <Text
                  whiteSpace="pre-wrap"
                  wordBreak="break-word"
                  fontSize="sm"
                  lineHeight="short"
                  textAlign="center"
                >
                  {t("pay with card")}
                </Text>
              </Button>
            )}
            <Button
              variant="solid"
              colorScheme="brand"
              aria-label={t("pay with invoice")}
              onClick={() => handleInvoicePayment()}
              flexDirection="column"
              alignItems="center"
              justifyContent="center"
              gap={2}
              p={2}
              h={24}
              w={24}
              isLoading={isSubmitting}
            >
              <Icon as={AiOutlineFileText} boxSize={8} />
              <Text
                whiteSpace="pre-wrap"
                wordBreak="break-word"
                fontSize="sm"
                lineHeight="short"
                textAlign="center"
              >
                {t("pay with invoice")}
              </Text>
            </Button>
          </>
        )}

        <Button
          variant="solid"
          colorScheme="gray"
          aria-label={t("view receipt")}
          onClick={() => openModal("receipt", laundryOrder)}
          flexDirection="column"
          alignItems="center"
          justifyContent="center"
          gap={2}
          p={2}
          h={24}
          w={24}
          isLoading={isSubmitting}
        >
          <Icon as={AiOutlineFileText} boxSize={8} />
          <Text
            whiteSpace="pre-wrap"
            wordBreak="break-word"
            fontSize="sm"
            lineHeight="short"
            textAlign="center"
          >
            {t("view receipt")}
          </Text>
        </Button>
      </Flex>

      <ReceiptModal
        laundryOrder={modalData}
        isOpen={modal === "receipt" && !!modalData}
        onClose={closeModal}
      />
      <ChangeStatusModal
        data={modalData}
        isOpen={modal === "changeStatus" && !!modalData}
        onClose={closeModal}
      />
      {/* TODO: modal update and mark as done */}
      {/* <UpdateAndChangeStatusModal
        laundryOrder={modalData}
        isOpen={modal === "updateAndChangeStatus" && !!modalData}
        onClose={closeModal}
      /> */}
    </>
  );
};

export default Buttons;
