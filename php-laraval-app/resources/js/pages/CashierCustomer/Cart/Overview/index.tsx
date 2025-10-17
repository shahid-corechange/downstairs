import { Box, Flex, Text } from "@chakra-ui/react";
import { Head, router } from "@inertiajs/react";
import { useEffect, useMemo } from "react";
import { Trans, useTranslation } from "react-i18next";
import {
  MdDeleteOutline,
  MdOutlineBookmarkRemove,
  MdOutlineEdit,
  MdOutlineStickyNote2,
} from "react-icons/md";

import AddCustomCartProductModal from "@/components/AddCustomCartProductModal";
import Alert from "@/components/Alert";
import DataTable from "@/components/DataTable";
import EditCartProductModal from "@/components/EditCartProductModal";
import ProductCatalogue from "@/components/ProductCatalogue";

import { NAVBAR_HEIGHT } from "@/constants/layout";
import { ServiceMembershipType } from "@/constants/service";

import { usePageModal } from "@/hooks/modal";
import useCart from "@/hooks/useCart";

import CashierLayout from "@/layouts/Cashier";

import useAuthStore from "@/stores/auth";

import { CartProduct, CartProductModalData } from "@/types/cartProduct";
import {
  AddCustomCartProductFormValues,
  AddCustomCartProductModalData,
} from "@/types/customCartProduct";
import FixedPrice from "@/types/fixedPrice";
import { LaundryOrder } from "@/types/laundryOrder";
import Product from "@/types/product";
import User from "@/types/user";

import { formatCurrency } from "@/utils/currency";

import { PageProps } from "@/types";

import getColumns from "./column";
import Buttons from "./components/Buttons";
import EmptyCartAlert from "./components/EmptyCartAlert";
import RemoveProductAlert from "./components/RemoveProductAlert";
import RemoveRutAlert from "./components/RemoveRutAlert";

type CashierCustomerCartProps = {
  laundryOrder: LaundryOrder;
  products: Product[];
  customer: User;
  discount: number;
  storeId: number;
  fixedPrice: FixedPrice | null;
};

const CashierCustomerCartPage = ({
  laundryOrder,
  products,
  customer,
  discount = 0,
  storeId,
  fixedPrice,
}: PageProps<CashierCustomerCartProps>) => {
  const { t } = useTranslation();
  const { currency, language } = useAuthStore();

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

  const fixedPriceProducts = useMemo(() => {
    const fixedPriceProducts = fixedPrice?.laundryProducts;

    if (!fixedPriceProducts) {
      return undefined;
    }

    return fixedPriceProducts?.length > 0 ? fixedPriceProducts : products;
  }, [fixedPrice?.laundryProducts, products]);

  const fixedPriceAmount = useMemo(
    () =>
      (fixedPrice?.rows || [])?.find(
        (row) => row.type === "laundry" || row.type === "cleaning and laundry",
      )?.priceWithVat || 0,
    [fixedPrice?.rows],
  );

  const {
    getCart,
    addToCart,
    removeFromCart,
    updateCart,
    clearCart,
    removeLaundryPreferenceFromCart,
  } = useCart();

  const cartKey = {
    userId: customer.id,
    storeId: storeId,
    laundryOrderId: laundryOrder?.id,
  };

  const {
    products: cartProducts,
    totalRut,
    totalPrice,
    fixedPrice: cartFixedPrice,
    hasFixedPrice: cartHasFixedPrice,
    hasRut: cartProductHasRut,
  } = getCart(cartKey);

  const membershipType = customer?.customers?.find(
    (customer) => customer.type === "primary",
  )?.membershipType;

  const columns = useMemo(
    () => getColumns(t, openCartProductModal),
    [t, openCartProductModal],
  );

  const handleProductChangeSubmit = (
    index: number,
    updates: Partial<CartProduct>,
  ) => {
    const oldProduct = products?.find((p) => p.id === cartProducts[index].id);

    updateCart({
      index,
      updates,
      oldProduct,
      cartKey,
    });
    closeCartProductModal();
  };

  const handleAddToCart = (product: Product, quantity: number) => {
    if (product.id === 0) {
      openModal("addCustomCartProduct", { quantity, products: cartProducts });
    } else {
      const newProduct = {
        ...product,
        hasRut: membershipType === "company" ? false : product.hasRut,
      };

      addToCart({
        product: newProduct,
        quantity,
        discount,
        cartKey,
        fixedPrice: fixedPriceAmount,
        fixedPriceProducts: fixedPriceProducts,
      });
    }
  };

  const handleAddCustomCartProductSubmit = (
    values: AddCustomCartProductFormValues,
  ) => {
    const product = {
      id: 0,
      name: values.name,
      hasRut: membershipType === "company" ? false : values.hasRut,
      priceWithVat: values.priceWithVat,
      vatGroup: values.vatGroup,
    } as Product;

    addToCart({
      product,
      quantity: values.quantity,
      discount: values.discount,
      note: values.note,
      cartKey,
    });
  };

  const handleGoToCheckout = () => {
    if (laundryOrder?.id) {
      router.get(`/cashier/customers/${customer.id}/orders/${laundryOrder.id}`);
    } else {
      router.get(`/cashier/customers/${customer.id}/cart/checkout`);
    }
  };

  useEffect(() => {
    removeLaundryPreferenceFromCart(cartKey);
  }, []);

  if (!customer?.id) {
    router.get(`/cashier/search`);
  }

  return (
    <>
      <Head>
        <title>{t("customer cart")}</title>
      </Head>
      <CashierLayout
        content={{
          p: 4,
          overflowY: "auto",
          h: `calc(100vh - ${NAVBAR_HEIGHT}px)`,
        }}
        customerId={customer.id}
      >
        <Flex w="full" gap={8} direction={{ base: "column", lg: "row" }}>
          <Box
            w={{ base: "100%", lg: "50%" }}
            h="full"
            order={{ base: 2, lg: 1 }}
          >
            <ProductCatalogue
              products={products}
              fixedPriceProducts={fixedPriceProducts}
              onAddToCartClick={handleAddToCart}
              withRut={membershipType === ServiceMembershipType.PRIVATE}
              withMiscProduct
            />
          </Box>

          <Flex
            direction="column"
            w={{ base: "100%", lg: "50%" }}
            gap={4}
            order={{ base: 1, lg: 2 }}
          >
            <Text fontSize="lg" fontWeight="bold">
              {t("customer name cart", { name: customer.fullname })}
            </Text>
            <DataTable
              title={t("customer cart")}
              data={cartProducts}
              columns={columns}
              searchable={false}
              filterable={false}
              paginatable={false}
              serverSide={false}
              footerTotal={[
                ...(cartHasFixedPrice
                  ? [
                      {
                        title: t("fixed price"),
                        value: cartFixedPrice,
                        formatter: (value: number) =>
                          formatCurrency(language, currency, value, 2),
                      },
                    ]
                  : []),
                {
                  title: t("total to pay"),
                  value: totalPrice,
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
                  isHidden: (row) =>
                    !row.original.hasRut || row.original.isFixedPrice,
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
              maxHeight="full"
            />

            {cartHasFixedPrice && (
              <Alert
                status="info"
                title={t("info")}
                richMessage={
                  <Trans
                    i18nKey="cart products containing fixed price"
                    values={{
                      total: formatCurrency(
                        language,
                        currency,
                        cartFixedPrice,
                        2,
                      ),
                    }}
                  />
                }
                fontSize="small"
              />
            )}

            {discount > 0 && (
              <Alert
                status="info"
                title={t("info")}
                richMessage={
                  <Trans
                    i18nKey="auto apply discount to cart products"
                    values={{
                      discount: discount,
                    }}
                  />
                }
                fontSize="small"
              />
            )}

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
                      i18nKey="cart products total rut"
                      values={{
                        total: formatCurrency(language, currency, totalRut, 2),
                      }}
                    />
                  }
                  fontSize="small"
                />
              </>
            )}

            <Buttons
              cartProducts={cartProducts}
              openCartProductModal={openCartProductModal}
              handleGoToCheckout={handleGoToCheckout}
            />
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
      <RemoveRutAlert
        data={cartProductModalData}
        isOpen={cartProductModal === "removeRut"}
        onClose={closeCartProductModal}
        cartProducts={cartProducts}
        products={products}
        cartKey={cartKey}
        updateCart={updateCart}
      />
      <RemoveProductAlert
        data={cartProductModalData}
        isOpen={cartProductModal === "removeProduct"}
        onClose={closeCartProductModal}
        cartKey={cartKey}
        removeFromCart={removeFromCart}
      />
      <EmptyCartAlert
        isOpen={cartProductModal === "emptyCart"}
        onClose={closeCartProductModal}
        cartKey={cartKey}
        clearCart={clearCart}
      />
    </>
  );
};

export default CashierCustomerCartPage;
