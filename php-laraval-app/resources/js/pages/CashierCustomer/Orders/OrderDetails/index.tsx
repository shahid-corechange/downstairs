import { Flex } from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { Head, router } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { FormProvider, useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import { NAVBAR_HEIGHT } from "@/constants/layout";

import useCart from "@/hooks/useCart";

import CashierLayout from "@/layouts/Cashier";

import { CartProduct } from "@/types/cartProduct";

import { PageProps } from "@/types";

import Buttons from "./components/Buttons";
import Cart from "./components/Cart";
import CustomerInfo from "./components/CustomerInfo";
import History from "./components/History";
import Message from "./components/Message";
import OrderInfo from "./components/OrderInfo";
import { CashierCustomerOrderDetailsProps, CheckoutFormType } from "./types";

type ProductValue = {
  productId: number;
  price: number;
} & Partial<CartProduct>;

const CashierCustomerOrderDetailsPage = ({
  customer,
  laundryOrder,
  laundryPreferences,
  fixedPrice,
}: PageProps<CashierCustomerOrderDetailsProps>) => {
  const { t } = useTranslation();

  const [isSubmitting, setIsSubmitting] = useState<boolean>(false);
  const [isAutoSubmitting, setIsAutoSubmitting] = useState<boolean>(false);

  const fixedPriceAmount = useMemo(
    () =>
      fixedPrice?.rows?.find(
        (row) => row.type === "laundry" || row.type === "cleaning and laundry",
      )?.priceWithVat || 0,
    [fixedPrice?.rows],
  );

  const {
    getCart,
    addLaundryPreferenceToCart,
    removeLaundryPreferenceFromCart,
    clearCart,
    addToCartBulk,
  } = useCart();

  const cartKey = useMemo(
    () => ({
      userId: customer.id,
      storeId: laundryOrder.storeId,
      laundryOrderId: laundryOrder.id,
    }),
    [customer.id, laundryOrder.storeId, laundryOrder.id],
  );

  const cart = getCart(cartKey);
  const { products: cartProducts, hasFixedPrice } = cart;

  const templateKey =
    laundryOrder.status === "done"
      ? "message done laundry order"
      : "message updated laundry order";

  const form = useForm<CheckoutFormType>({
    defaultValues: {
      laundryPreferenceId: laundryOrder.laundryPreferenceId,
      pickupScheduleId: laundryOrder.pickupInCleaningId,
      deliveryScheduleId: laundryOrder.deliveryInCleaningId,
      sendMessage: false,
      message: t(templateKey, { code: laundryOrder.id }),
    },
  });

  const handleSubmit = form.handleSubmit((values, e) => {
    setIsSubmitting(true);

    const newValues = {
      ...values,
      ...(laundryOrder.status !== "pending" && {
        laundryPreferenceId: undefined,
        pickupScheduleId: undefined,
      }),
      ...([
        "in_progress_delivery",
        "delivered",
        "done",
        "paid",
        "closed",
      ].includes(laundryOrder.status) && {
        deliveryScheduleId: undefined,
      }),
      userId: laundryOrder.userId,
      products: cartProducts.reduce((acc, product) => {
        if (!product.isLaundryPreference) {
          acc.push({
            productId: product.id,
            name: product.name,
            note: product.note,
            quantity: product.quantity,
            price: product.priceWithVat / (1 + product.vatGroup / 100),
            vatGroup: product.vatGroup,
            discount: product.discount,
            hasRut: product.hasRut,
            isModified: product.isModified,
          });
        }
        return acc;
      }, [] as ProductValue[]),
    };

    router.patch(`/cashier/orders/${laundryOrder.id}`, newValues, {
      onFinish: () => {
        setIsSubmitting(false);
        setIsAutoSubmitting(false);
      },
      onSuccess: (page) => {
        const {
          flash: { error },
        } = (page as Page<PageProps>).props;

        if (error) {
          return;
        }

        // Only clear cart and navigate if it is a submit event
        if (e?.type === "submit") {
          clearCart(cartKey);
          router.get("/cashier/search");
        }
      },
    });
  });

  useEffect(() => {
    const products = cartProducts.filter(
      (product) => !product.isLaundryPreference,
    );

    if (products.length > 0 && laundryOrder.laundryPreference) {
      // Add laundry preference to cart
      addLaundryPreferenceToCart({
        cartKey,
        laundryPreference: laundryOrder.laundryPreference,
      });

      // Auto-submit when cart products is different from laundry order products
      // Filter out laundry preferences from cart products for comparison
      const cartProductsToCompare = cartProducts.filter(
        (product) => !product.isLaundryPreference,
      );

      // Check if the number of products is different
      if (cartProductsToCompare.length !== laundryOrder.products?.length) {
        const timeoutId = setTimeout(() => {
          setIsAutoSubmitting(true);
          handleSubmit();
        }, 1000);

        return () => clearTimeout(timeoutId);
      }

      // Compare each product in detail
      const hasChanges = cartProductsToCompare.some((cProduct, index) => {
        const loProduct = laundryOrder.products?.[index];

        if (!loProduct) {
          return true; // Missing product means there are changes
        }

        return (
          loProduct.name !== cProduct.name ||
          loProduct.quantity !== cProduct.quantity ||
          Boolean(loProduct.hasRut) !== Boolean(cProduct.hasRut) ||
          loProduct.priceWithVat !== cProduct.priceWithVat ||
          loProduct.vatGroup !== cProduct.vatGroup ||
          loProduct.discount !== cProduct.discount
        );
      });

      if (hasChanges) {
        // Debounce the submit to avoid excessive API calls
        const timeoutId = setTimeout(() => {
          setIsAutoSubmitting(true);
          handleSubmit();
        }, 1000);

        return () => clearTimeout(timeoutId);
      }
    } else if (laundryOrder.products && laundryOrder.laundryPreference) {
      clearCart(cartKey);
      addToCartBulk({
        cartKey,
        products: laundryOrder.products,
        fixedPrice: fixedPriceAmount,
        fixedPriceProducts: fixedPrice?.laundryProducts,
      });
      addLaundryPreferenceToCart({
        cartKey,
        laundryPreference: laundryOrder.laundryPreference,
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Auto-submit when form values change (with debouncing to prevent excessive calls)
  useEffect(() => {
    const subscription = form.watch((_, { name, type }) => {
      // Only auto-submit on actual field changes, not on form initialization
      if (type === "change" && name && !isAutoSubmitting) {
        // Debounce the submit to avoid excessive API calls
        const timeoutId = setTimeout(() => {
          setIsAutoSubmitting(true);
          handleSubmit();
        }, 1000);

        return () => clearTimeout(timeoutId);
      }
    });

    return () => subscription.unsubscribe();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [form, handleSubmit]);

  return (
    <>
      <Head>
        <title>{t("customer order details")}</title>
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
                <CustomerInfo
                  userId={customer.id}
                  customer={laundryOrder.customer}
                />
                <OrderInfo
                  laundryOrder={laundryOrder}
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
                  isSubmitting={isSubmitting}
                />
                {laundryOrder.status !== "closed" && (
                  <Message
                    notificationMethod={customer?.info?.notificationMethod}
                  />
                )}
                <History laundryOrderHistory={laundryOrder.histories} />
              </Flex>
              <Flex direction="column" w="50%" gap={4}>
                <Cart user={laundryOrder.user} cart={cart} />
                <Buttons
                  laundryOrder={laundryOrder}
                  isSubmitting={isSubmitting}
                />
              </Flex>
            </Flex>
          </Flex>
        </FormProvider>
      </CashierLayout>
    </>
  );
};

export default CashierCustomerOrderDetailsPage;
