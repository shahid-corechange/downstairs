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
  ) => {
    const isEditable =
      !context.row.original.isFixedPrice ||
      ["quantity", "note"].includes(context.column.id);

    return (
      <Text
        cursor={isEditable ? "pointer" : undefined}
        onClick={() => {
          if (!isEditable) {
            return;
          }

          openEditCartProductModal("editCartProduct", {
            index: context.row.index,
            key: context.column.id as keyof EditCartProductFormValues,
            product: context.row.original,
            cartProducts: context.table
              .getRowModel()
              .rows.map((row) => row.original),
          });
        }}
        {...(context.column.id === "priceWithVat" && {
          display: "flex",
          flexDirection: "column",
          alignItems: "flex-end",
        })}
        _hover={{
          textDecoration: isEditable ? "underline" : undefined,
        }}
      >
        {originalValue}
      </Text>
    );
  };

  return createColumnDefs<CartProduct>(({ createData }) => [
    createData("name", {
      label: t("name"),
      render: (value, _, context) => {
        const { hasRut, isFixedPrice } = context.row.original;

        if (isFixedPrice) {
          return renderData(`${value} (${t("fixed price")})`, "", context);
        }

        if (hasRut) {
          return renderData(`${value} (${t("rut")})`, "", context);
        }

        return renderData(value, "", context);
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
        const { hasRut, isFixedPrice } = context.row.original;
        const priceWithoutRut = formatCurrency(language, currency, value, 2);

        if (isFixedPrice) {
          return (
            <Text as="span" textDecoration="line-through" color="gray.500">
              {priceWithoutRut}
            </Text>
          );
        }

        if (hasRut) {
          const price = value * RUT_DISCOUNT;
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
