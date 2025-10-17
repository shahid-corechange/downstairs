import { Flex } from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { Head, router, usePage } from "@inertiajs/react";
import { useEffect, useState } from "react";
import { FormProvider, useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import { NAVBAR_HEIGHT } from "@/constants/layout";

import useCart from "@/hooks/useCart";

import CashierLayout from "@/layouts/Cashier";

import { PageProps } from "@/types";

import Buttons from "./components/Buttons";
import Cart from "./components/Cart";
import CustomerInfo from "./components/CustomerInfo";
import Message from "./components/Message";
import OrderInfo from "./components/OrderInfo";
import { CashierCustomerCheckoutProps, CheckoutFormType } from "./types";

const CashierCustomerCheckoutPage = ({
  laundryPreferences,
  customer,
  storeId,
}: PageProps<CashierCustomerCheckoutProps>) => {
  const { t } = useTranslation();
  const [isSubmitting, setIsSubmitting] = useState<boolean>(false);
  const { errors: serverErrors } = usePage<PageProps>().props;

  const {
    getCart,
    clearCart,
    addLaundryPreferenceToCart,
    removeLaundryPreferenceFromCart,
  } = useCart();

  const cartKey = {
    userId: customer.id,
    storeId: storeId,
  };

  const cart = getCart(cartKey);
  const { products: cartProducts, hasFixedPrice } = cart;

  const form = useForm<CheckoutFormType>({
    defaultValues: {
      laundryPreferenceId: laundryPreferences?.[0]?.id,
      userId: customer.id,
      sendMessage: true,
    },
  });

  const customerPrimary = customer?.customers?.find(
    (customer) => customer.type === "primary",
  );

  const handleSubmit = form.handleSubmit((values) => {
    setIsSubmitting(true);

    const products = cartProducts
      .filter((product) => !product.isLaundryPreference)
      .map((product) => ({
        ...product,
        productId: product.id,
        price: product.priceWithVat / (1 + product.vatGroup / 100),
      }));

    const payload = {
      laundryPreferenceId: values.laundryPreferenceId,
      userId: values.userId,
      pickupScheduleId: values.pickupScheduleId,
      deliveryScheduleId: values.deliveryScheduleId,
      sendMessage: values.sendMessage,
      message: values.message,
      products,
    };

    router.post(`/cashier/orders`, payload, {
      onFinish: () => setIsSubmitting(false),
      onSuccess: (page) => {
        const {
          flash: { error },
        } = (page as Page<PageProps>).props;

        if (!error) {
          clearCart(cartKey);
        }
      },
    });
  });

  useEffect(() => {
    form.setValue("products", cartProducts);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [cartProducts]);

  useEffect(() => {
    if (cartProducts.length === 0) {
      router.get(`/cashier/customers/${customer.id}/cart`);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <>
      <Head>
        <title>{t("customer checkout")}</title>
      </Head>
      <CashierLayout
        content={{
          p: 4,
          overflowY: "auto",
          h: `calc(100vh - ${NAVBAR_HEIGHT}px)`,
        }}
        customerId={customer.id}
      >
        <FormProvider {...form}>
          <Flex
            as="form"
            direction="column"
            gap={4}
            onSubmit={handleSubmit}
            autoComplete="off"
            noValidate
          >
            <Flex justify="space-between" w="full" gap={8}>
              <Flex direction="column" w="50%" h="full" gap={4}>
                <CustomerInfo user={customer} />
                <OrderInfo
                  customer={customerPrimary}
                  laundryPreferences={laundryPreferences}
                  hasFixedPrice={hasFixedPrice}
                  addLaundryPreferenceToCart={(laundryPreference) =>
                    addLaundryPreferenceToCart({
                      cartKey,
                      laundryPreference,
                    })
                  }
                  removeLaundryPreferenceFromCart={() =>
                    removeLaundryPreferenceFromCart(cartKey)
                  }
                />
                <Message
                  notificationMethod={customer?.info?.notificationMethod}
                />
              </Flex>
              <Flex direction="column" w="50%" gap={4}>
                <Cart user={customer} cart={cart} errors={serverErrors} />
                <Buttons customerId={customer.id} isSubmitting={isSubmitting} />
              </Flex>
            </Flex>
          </Flex>
        </FormProvider>
      </CashierLayout>
    </>
  );
};

export default CashierCustomerCheckoutPage;
