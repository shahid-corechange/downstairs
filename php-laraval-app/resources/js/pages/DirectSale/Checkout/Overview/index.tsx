import { Button, Flex, Icon, Text } from "@chakra-ui/react";
import { Head, router } from "@inertiajs/react";
import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { AiOutlineArrowLeft, AiOutlineDollarCircle } from "react-icons/ai";

import { NAVBAR_HEIGHT } from "@/constants/layout";

import useDirectSaleCart from "@/hooks/useDirectSaleCart";

import CashierLayout from "@/layouts/Cashier";

import { PageProps } from "@/types";

import Cart from "./components/Cart";
import CustomerInfo from "./components/CustomerInfo";
import OrderInfo from "./components/OrderInfo";
import { DirectSaleCheckoutProps } from "./types";

export type CustomerSale = {
  userId?: number;
  customerId?: number;
};

const DirectSaleCheckoutPage = ({
  storeId,
}: PageProps<DirectSaleCheckoutProps>) => {
  const { t } = useTranslation();

  const { getCart, addCustomer } = useDirectSaleCart();
  const cartKey = { storeId };
  const {
    products: cartProducts,
    totalPrice,
    userId,
    customerId,
  } = getCart(cartKey);

  const [customer, setCustomer] = useState<CustomerSale | undefined>({
    userId,
    customerId,
  });

  const handleCardPayment = () => {
    router.get("/cashier/direct-sales/cart/checkout/card-payment");
  };

  const handleGoToCart = () => {
    router.get("/cashier/direct-sales/cart");
  };

  useEffect(() => {
    if (cartProducts && cartProducts.length === 0) {
      setTimeout(() => {
        handleGoToCart();
      }, 500);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    if (customer?.userId && customer?.customerId) {
      addCustomer({
        cartKey,
        userId: customer.userId,
        customerId: customer.customerId,
      });
    }
  }, [customer]);

  return (
    <>
      <Head>
        <title>{t("direct sale checkout")}</title>
      </Head>
      <CashierLayout
        content={{
          p: 4,
          overflowY: "auto",
          h: `calc(100vh - ${NAVBAR_HEIGHT}px)`,
        }}
      >
        <Flex justify="space-between" w="full" gap={8}>
          <Flex direction="column" w="50%" h="full" gap={4}>
            <OrderInfo />
            <CustomerInfo
              customerSale={customer}
              setCustomerSale={(customer) => setCustomer(customer)}
            />
          </Flex>
          <Flex
            direction="column"
            w="50%"
            gap={4}
            pt={3}
            position="sticky"
            top={0}
            maxH={`calc(100vh - ${NAVBAR_HEIGHT}px - 2rem)`}
            overflowY="auto"
          >
            <Cart cartProducts={cartProducts} totalPrice={totalPrice} />

            <Flex justify="flex-end" w="full" gap={4}>
              <Button
                variant="solid"
                colorScheme="gray"
                aria-label={t("back to cart")}
                onClick={handleGoToCart}
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
                variant="solid"
                colorScheme="yellow"
                aria-label={t("pay with card")}
                flexDirection="column"
                alignItems="center"
                justifyContent="center"
                gap={2}
                p={2}
                h={24}
                w={24}
                onClick={handleCardPayment}
                isDisabled={!customer?.userId}
              >
                <Icon as={AiOutlineDollarCircle} boxSize={8} />
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
            </Flex>
          </Flex>
        </Flex>
      </CashierLayout>
    </>
  );
};

export default DirectSaleCheckoutPage;
