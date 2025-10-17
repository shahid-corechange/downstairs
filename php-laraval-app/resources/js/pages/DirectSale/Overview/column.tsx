import { Text } from "@chakra-ui/react";
import { CellContext } from "@tanstack/react-table";
import { TFunction } from "i18next";

import { RUT_DISCOUNT } from "@/constants/rut";

import useAuthStore from "@/stores/auth";

import {
  CartProduct,
  CartProductModalData,
  EditCartProductFormValues,
} from "@/types/cartProduct";

import { formatCurrency } from "@/utils/currency";
import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (
  t: TFunction,
  openEditCartProductModal: (
    type: "editCartProduct",
    modalData: CartProductModalData,
  ) => void,
) => {
  const { currency, language } = useAuthStore.getState();

  const renderData = (
    originalValue: number | string | React.ReactNode,
    _: string,
    context: CellContext<CartProduct, number | string>,
  ) => (
    <Text
      cursor="pointer"
      onClick={() =>
        openEditCartProductModal("editCartProduct", {
          index: context.row.index,
          key: context.column.id as keyof EditCartProductFormValues,
          product: context.row.original,
        })
      }
      {...(context.column.id === "priceWithVat" && {
        display: "flex",
        flexDirection: "column",
        alignItems: "flex-end",
      })}
      _hover={{
        textDecoration: "underline",
      }}
    >
      {originalValue}
    </Text>
  );

  return createColumnDefs<CartProduct>(({ createData }) => [
    createData("name", {
      label: t("name"),
      render: (value, _, context) => {
        const hasRut = context.row.original.hasRut;
        const name = hasRut ? `${value} (${t("rut")})` : value;

        return renderData(name, "", context);
      },
    }),
    createData("quantity", {
      label: t("qty"),
      display: "number",
      render: renderData,
    }),
    createData("priceWithVat", {
      label: t("price"),
      display: "currency",
      render: (value, _, context) => {
        const hasRut = context.row.original.hasRut;
        const priceWithoutRut = formatCurrency(language, currency, value, 2);

        if (hasRut) {
          const price = hasRut ? value * RUT_DISCOUNT : value;
          const priceWithRut = formatCurrency(language, currency, price, 2);

          const priceDisplay = (
            <>
              {priceWithRut}
              <Text
                as="span"
                fontSize="x-small"
                w="fit-content"
                textDecoration={hasRut ? "line-through" : undefined}
                color={hasRut ? "gray.500" : undefined}
                wordBreak="keep-all"
              >
                {priceWithoutRut}
              </Text>
            </>
          );

          return renderData(priceDisplay, "", context);
        }

        return renderData(priceWithoutRut, "", context);
      },
    }),
    createData("vatGroup", { label: t("vat"), display: "number" }),
    createData("discount", {
      label: t("discount"),
      render: (value, _, context) => renderData(`${value}%`, "", context),
    }),
    createData("totalPrice", { label: t("total"), display: "currency" }),
  ]);
};

export default getColumns;
