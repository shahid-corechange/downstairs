import { Box, Text } from "@chakra-ui/react";
import { TFunction } from "i18next";

import { RUT_DISCOUNT } from "@/constants/rut";

import { CartProduct } from "@/types/cartProduct";

import { formatCurrency } from "@/utils/currency";
import { createColumnDefs } from "@/utils/dataTable";

const getColumns = ({
  language,
  currency,
  t,
  errors,
}: {
  language: string;
  currency: string;
  t: TFunction;
  errors?: Record<string, string>;
}) => {
  return createColumnDefs<CartProduct>(({ createData }) => [
    createData("name", {
      label: t("name"),
      render: (value, _, context) => {
        const { hasRut, isFixedPrice } = context.row.original;
        const rowIndex = context.row.index;

        // Try different error key formats
        const productError =
          errors?.[`products.${rowIndex}.productId`] ||
          errors?.[`products.${rowIndex}.product_id`] ||
          errors?.[`products.${rowIndex}.id`] ||
          errors?.[`products.${rowIndex}`] ||
          // Try with different field names
          errors?.[`products.${rowIndex}.productId`] ||
          errors?.[`products.${rowIndex}.product_id`] ||
          errors?.[`products.${rowIndex}.name`] ||
          errors?.[`products.${rowIndex}.quantity`] ||
          errors?.[`products.${rowIndex}.price`] ||
          errors?.[`products.${rowIndex}.vatGroup`] ||
          errors?.[`products.${rowIndex}.vat_group`] ||
          errors?.[`products.${rowIndex}.discount`] ||
          errors?.[`products.${rowIndex}.hasRut`] ||
          errors?.[`products.${rowIndex}.has_rut`] ||
          errors?.[`products.${rowIndex}.isModified`] ||
          errors?.[`products.${rowIndex}.is_modified`];

        let displayName = value;
        if (isFixedPrice) {
          displayName = `${value} (${t("fixed price")})`;
        } else if (hasRut) {
          displayName = `${value} (${t("rut")})`;
        }

        return (
          <Box>
            <Text>{displayName}</Text>
            {productError && (
              <Text fontSize="xs" color="red.500" mt={1} fontWeight="medium">
                {productError}
              </Text>
            )}
          </Box>
        );
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
};

export default getColumns;
