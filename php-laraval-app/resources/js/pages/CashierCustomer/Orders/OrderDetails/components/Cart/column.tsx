import { Text } from "@chakra-ui/react";
import { TFunction } from "i18next";

import { RUT_DISCOUNT } from "@/constants/rut";

import { CartProduct } from "@/types/cartProduct";

import { formatCurrency } from "@/utils/currency";
import { createColumnDefs } from "@/utils/dataTable";

const getColumns = ({
  language,
  currency,
  t,
}: {
  language: string;
  currency: string;
  t: TFunction;
}) =>
  createColumnDefs<CartProduct>(({ createData }) => [
    createData("name", {
      label: t("name"),
      render: (value, _, context) => {
        const { hasRut, isFixedPrice } = context.row.original;

        if (isFixedPrice) {
          return `${value} (${t("fixed price")})`;
        }

        if (hasRut) {
          return `${value} (${t("rut")})`;
        }

        return value;
      },
    }),
    createData("quantity", {
      label: t("qty"),
      display: "number",
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

          return (
            <Text>
              {priceWithRut} <br />
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
            </Text>
          );
        }

        return priceWithoutRut;
      },
    }),
    createData("vatGroup", { label: t("vat"), display: "number" }),
    createData("discount", {
      label: t("discount"),
      render: (value) => `${value}%`,
    }),
    createData("totalPrice", { label: t("total"), display: "currency" }),
  ]);

export default getColumns;
