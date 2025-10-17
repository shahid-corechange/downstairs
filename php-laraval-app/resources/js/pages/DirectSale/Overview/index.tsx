import { Box, Button, Flex, Icon, Text } from "@chakra-ui/react";
import { Head, router } from "@inertiajs/react";
import { useCallback, useEffect, useMemo } from "react";
import { Trans, useTranslation } from "react-i18next";
import {
  MdDeleteOutline,
  MdOutlineBookmarkRemove,
  MdOutlineEdit,
  MdOutlineShoppingCartCheckout,
  MdOutlineStickyNote2,
} from "react-icons/md";

import AddCustomCartProductModal from "@/components/AddCustomCartProductModal";
import Alert from "@/components/Alert";
import AlertDialog from "@/components/AlertDialog";
import DataTable from "@/components/DataTable";
import EditCartProductModal from "@/components/EditCartProductModal";
import ProductCatalogue from "@/components/ProductCatalogue";

import { NAVBAR_HEIGHT } from "@/constants/layout";

import { usePageModal } from "@/hooks/modal";
import useDirectSaleCart from "@/hooks/useDirectSaleCart";

import CashierLayout from "@/layouts/Cashier";

import useAuthStore from "@/stores/auth";

import { CartProduct, CartProductModalData } from "@/types/cartProduct";
import {
  AddCustomCartProductFormValues,
  AddCustomCartProductModalData,
} from "@/types/customCartProduct";
import Product from "@/types/product";

import { formatCurrency } from "@/utils/currency";
import { round } from "@/utils/number";

import { PageProps } from "@/types";

import getColumns from "./column";

type DirectSaleProps = {
  products: Product[];
  storeId: number;
};

const DirectSalePage = ({ products, storeId }: PageProps<DirectSaleProps>) => {
  const { t } = useTranslation();
  const { currency, language } = useAuthStore();

  const {
    getCart,
    addToCart,
    removeFromCart,
    updateCart,
    clearCart,
    removeCustomer,
  } = useDirectSaleCart();
  const cartKey = { storeId };
  const {
    products: cartProducts,
    totalRut,
    hasRut: cartProductHasRut,
    totalPrice,
  } = getCart(cartKey);

  const { roundedTotalToPay, roundAmount } = useMemo(() => {
    const roundedTotalToPay = Math.round(totalPrice);
    const roundAmount = roundedTotalToPay - totalPrice;

    return {
      roundedTotalToPay,
      roundAmount,
    };
  }, [totalPrice]);

  const { modal, modalData, openModal, closeModal } = usePageModal<
    AddCustomCartProductModalData,
    "addCustomCartProduct"
  >();

  const {
    modal: cartProductModal,
    modalData: cartProductModalData,
    openModal: openCartProductModal,
    closeModal: closeCartProductModal,
  } = usePageModal<
    CartProductModalData,
    "editCartProduct" | "removeProduct" | "removeRut" | "emptyCart"
  >();

  const columns = useMemo(
    () => getColumns(t, openCartProductModal),
    [t, openCartProductModal],
  );

  const handleProductChangeSubmit = (
    index: number,
    updates: Partial<CartProduct>,
  ) => {
    const product = products.find(
      (product) => product.id === cartProducts[index].id,
    );

    updateCart({
      cartKey,
      index,
      updates,
      oldProduct: product,
    });
    closeCartProductModal();
  };

  const handleAddToCart = useCallback(
    (product: Product, quantity: number) => {
      if (product.id === 0) {
        openModal("addCustomCartProduct", { quantity, products: cartProducts });
      } else {
        addToCart({
          cartKey,
          product,
          quantity,
        });
      }
    },
    [cartKey, openModal, addToCart],
  );

  const handleAddCustomCartProductSubmit = (
    values: AddCustomCartProductFormValues,
  ) => {
    const product = {
      id: 0,
      name: values.name,
      hasRut: values.hasRut,
      priceWithVat: values.priceWithVat,
      vatGroup: values.vatGroup,
    } as Product;

    addToCart({
      cartKey,
      product,
      quantity: values.quantity,
      discount: values.discount,
      note: values.note,
    });
  };

  const handleGoToCheckout = () => {
    router.get("/cashier/direct-sales/cart/checkout");
  };

  const handleEmptyCart = useCallback(() => {
    openCartProductModal("emptyCart");
  }, [openCartProductModal]);

  useEffect(() => {
    removeCustomer({
      cartKey,
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <>
      <Head>
        <title>{t("direct sales")}</title>
      </Head>
      <CashierLayout
        content={{
          p: 4,
          overflowY: "auto",
          h: `calc(100vh - ${NAVBAR_HEIGHT}px)`,
        }}
      >
        <Flex justify="space-between" w="full" gap={8}>
          <Box w="50%" h="full">
            <ProductCatalogue
              products={products}
              onAddToCartClick={handleAddToCart}
              withMiscProduct
            />
          </Box>

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
            <DataTable
              title={t("direct sales")}
              data={cartProducts}
              columns={columns}
              searchable={false}
              filterable={false}
              paginatable={false}
              serverSide={false}
              footerTotal={[
                ...(round(roundAmount) !== 0
                  ? [
                      {
                        title: t("rounding"),
                        value: roundAmount,
                        formatter: (value: number) =>
                          formatCurrency(language, currency, value, 2),
                      },
                    ]
                  : []),
                {
                  title: t("total to pay"),
                  value: roundedTotalToPay,
                  formatter: (value: number) =>
                    formatCurrency(language, currency, value, 2),
                },
              ]}
              actions={[
                {
                  label: t("edit"),
                  icon: MdOutlineEdit,
                  onClick: (row) => {
                    openCartProductModal("editCartProduct", {
                      index: row.index,
                      key: "name",
                      product: row.original,
                      cartProducts: cartProducts,
                    });
                  },
                },
                {
                  label: t("remove rut"),
                  icon: MdOutlineBookmarkRemove,
                  isHidden: (row) => !row.original.hasRut,
                  onClick: (row) => {
                    openCartProductModal("removeRut", {
                      index: row.index,
                      key: "name",
                      product: row.original,
                    });
                  },
                },
                {
                  label: t("note"),
                  icon: MdOutlineStickyNote2,
                  onClick: (row) => {
                    openCartProductModal("editCartProduct", {
                      index: row.index,
                      key: "note",
                      product: row.original,
                      cartProducts: cartProducts,
                    });
                  },
                },
                {
                  label: t("remove product"),
                  icon: MdDeleteOutline,
                  onClick: (row) => {
                    openCartProductModal("removeProduct", {
                      index: row.index,
                      key: "name",
                      product: row.original,
                    });
                  },
                },
              ]}
              size="xs"
              useWindowScroll
            />

            {cartProductHasRut && (
              <>
                <Alert
                  status="info"
                  title={t("info")}
                  message={t("cart products containing rut")}
                  fontSize="small"
                />
                <Alert
                  status="info"
                  title={t("info")}
                  richMessage={
                    <Trans
                      i18nKey="total square footage"
                      values={{
                        total: formatCurrency(language, currency, totalRut, 2),
                      }}
                    />
                  }
                  fontSize="small"
                />
              </>
            )}

            {round(roundAmount) !== 0 && (
              <>
                <Alert
                  status="info"
                  title={t("info")}
                  richMessage={
                    <Trans
                      i18nKey="cart products total to pay rounded"
                      values={{
                        roundAmount: formatCurrency(
                          language,
                          currency,
                          roundAmount,
                          2,
                        ),
                      }}
                    />
                  }
                  fontSize="small"
                />
              </>
            )}

            <Flex justify="flex-end" w="full" gap={4}>
              {cartProducts.length > 0 && (
                <Button
                  variant="solid"
                  colorScheme="gray"
                  aria-label={t("empty cart")}
                  onClick={handleEmptyCart}
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
                onClick={handleGoToCheckout}
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
          </Flex>
        </Flex>
      </CashierLayout>

      <AddCustomCartProductModal
        data={modalData}
        isOpen={modal === "addCustomCartProduct"}
        onClose={closeModal}
        handleAddCustomCartProductSubmit={handleAddCustomCartProductSubmit}
      />
      <EditCartProductModal
        data={cartProductModalData}
        isOpen={cartProductModal === "editCartProduct"}
        onClose={closeCartProductModal}
        handleProductChangeSubmit={handleProductChangeSubmit}
      />
      <AlertDialog
        title={t("remove rut")}
        confirmButton={{
          colorScheme: "red",
        }}
        isOpen={cartProductModal === "removeRut"}
        onClose={closeCartProductModal}
        onConfirm={() => {
          if (cartProductModalData?.index !== undefined) {
            const product = products.find(
              (product) =>
                product.id === cartProducts[cartProductModalData.index].id,
            );

            updateCart({
              cartKey,
              index: cartProductModalData.index,
              updates: { hasRut: false },
              oldProduct: product,
            });
          }
          closeCartProductModal();
        }}
      >
        <Trans i18nKey="remove rut alert body" />
      </AlertDialog>
      <AlertDialog
        title={t("remove product")}
        confirmButton={{
          colorScheme: "red",
        }}
        isOpen={cartProductModal === "removeProduct"}
        onClose={closeCartProductModal}
        onConfirm={() => {
          if (cartProductModalData?.index !== undefined) {
            removeFromCart({
              index: cartProductModalData.index,
              cartKey,
            });
          }
          closeCartProductModal();
        }}
      >
        <Trans i18nKey="remove cart product alert body" />
      </AlertDialog>
      <AlertDialog
        title={t("empty cart")}
        confirmButton={{
          colorScheme: "red",
        }}
        isOpen={cartProductModal === "emptyCart"}
        onClose={closeCartProductModal}
        onConfirm={() => {
          clearCart(cartKey);
          closeCartProductModal();
        }}
      >
        <Trans i18nKey="empty cart alert body" />
      </AlertDialog>
    </>
  );
};

export default DirectSalePage;
