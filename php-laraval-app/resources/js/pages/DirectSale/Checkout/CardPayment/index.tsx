import {
  Button,
  Card,
  CardBody,
  CardHeader,
  Flex,
  Heading,
  Icon,
  useDisclosure,
} from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { Head, router } from "@inertiajs/react";
import { useCallback, useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { FaCcMastercard, FaCcVisa } from "react-icons/fa";

import BrandText from "@/components/BrandText";

import { PaymentMethod } from "@/constants/paymentMethod";

import useDirectSaleCart from "@/hooks/useDirectSaleCart";

import CashierLayout from "@/layouts/Cashier";

import useAuthStore from "@/stores/auth";

import StoreSale from "@/types/storeSale";

import { formatCurrency } from "@/utils/currency";

import { PageProps } from "@/types";

import InfoRow from "./components/InfoRow";
import ReceiptModal from "./components/ReceiptModal";

type CardPaymentPageProps = {
  storeId: string;
};

type SuccessPayload = {
  storeSale: StoreSale;
};

const CardPayment = ({ storeId }: PageProps<CardPaymentPageProps>) => {
  const { t } = useTranslation();
  const { language, currency } = useAuthStore();

  const [storeSaleId, setStoreSaleId] = useState<number>();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isPaid, setIsPaid] = useState(false);
  const [totalToPay, setTotalToPay] = useState<number>(0);
  const [hasCheckedCart, setHasCheckedCart] = useState(false);

  const {
    isOpen: isReceiptOpen,
    onOpen: onReceiptOpen,
    onClose: onReceiptClose,
  } = useDisclosure();

  const { getCart, clearCart } = useDirectSaleCart();
  const cartKey = { storeId };
  const {
    products: cartProducts,
    totalPrice,
    userId,
    customerId,
  } = getCart(cartKey);
  const roundedTotalToPay = Math.round(totalPrice);

  const handleCancel = () => {
    if (!cartProducts || cartProducts.length === 0) {
      handleFinish();
      return;
    }

    router.get("/cashier/direct-sales/cart/checkout");
  };

  const handleFinish = () => {
    router.get("/cashier/direct-sales/cart");
  };

  const handlePay = useCallback(() => {
    setIsSubmitting(true);

    const payload = {
      userId: userId?.toString(),
      customerId: customerId?.toString(),
      paymentMethod: PaymentMethod.CREDIT_CARD,
      products: cartProducts.map((product) => ({
        ...product,
        productId: product.id,
        price: product.priceWithVat / (1 + product.vatGroup / 100),
      })),
    };

    router.post(`/cashier/direct-sales/cart/checkout`, payload, {
      onFinish: () => {
        setIsSubmitting(false);
      },
      onSuccess: (page) => {
        const {
          flash: { successPayload },
        } = (
          page as Page<
            PageProps<
              Record<string, unknown>,
              SuccessPayload | undefined,
              unknown
            >
          >
        ).props;

        const { storeSale } = successPayload ?? {};
        setIsPaid(true);

        if (storeSale) {
          clearCart(cartKey);
          setStoreSaleId(storeSale.id);
          setTotalToPay(storeSale.roundedTotalToPay);
          onReceiptOpen();
        }
      },
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [cartProducts, clearCart]);

  useEffect(() => {
    if (!hasCheckedCart && cartProducts && cartProducts.length === 0) {
      setTimeout(() => {
        handleFinish();
      }, 500);
    }
    setHasCheckedCart(true);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [cartProducts]);

  return (
    <>
      <Head>
        <title>{t("card payment")}</title>
      </Head>
      <CashierLayout content={{ p: 4 }}>
        <Flex direction="column" mb={8}>
          <BrandText text={t("payment")} />
        </Flex>

        <Card w="50%">
          <CardHeader>
            <Heading size="sm">{t("order info")}</Heading>
          </CardHeader>
          <CardBody fontSize="sm">
            <Flex direction="column" gap={4}>
              <InfoRow
                label={t("amount")}
                value={formatCurrency(
                  language,
                  currency,
                  isPaid ? totalToPay : roundedTotalToPay,
                  2,
                )}
              />
              <InfoRow
                label={t("allowed cards")}
                value={
                  <Flex gap={2} alignItems="center">
                    <Icon
                      as={FaCcVisa}
                      boxSize={12}
                      color="brand.600"
                      _dark={{ color: "brand.100" }}
                    />
                    <Icon
                      as={FaCcMastercard}
                      boxSize={12}
                      color="brand.600"
                      _dark={{ color: "brand.100" }}
                    />
                  </Flex>
                }
              />

              <Flex justify="flex-end" gap={4} mt={4}>
                {isPaid ? (
                  <>
                    <Button
                      colorScheme="gray"
                      fontSize="sm"
                      onClick={onReceiptOpen}
                    >
                      {t("receipt")}
                    </Button>
                    <Button
                      colorScheme="brand"
                      fontSize="sm"
                      onClick={handleFinish}
                    >
                      {t("back to cart")}
                    </Button>
                  </>
                ) : (
                  <>
                    <Button
                      colorScheme="red"
                      fontSize="sm"
                      onClick={handleCancel}
                    >
                      {t("cancel")}
                    </Button>
                    <Button
                      colorScheme="brand"
                      fontSize="sm"
                      onClick={handlePay}
                      isLoading={isSubmitting}
                      isDisabled={!cartProducts || cartProducts.length === 0}
                    >
                      {t("complete payment")}
                    </Button>
                  </>
                )}
              </Flex>
            </Flex>
          </CardBody>
        </Card>
      </CashierLayout>

      <ReceiptModal
        isOpen={isReceiptOpen}
        onClose={onReceiptClose}
        storeSaleId={storeSaleId}
      />
    </>
  );
};

export default CardPayment;
