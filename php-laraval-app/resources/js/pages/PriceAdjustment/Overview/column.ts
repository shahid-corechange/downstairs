import { TFunction } from "i18next";

import PriceAdjustment from "@/types/priceAdjustment";

import { formatCurrency } from "@/utils/currency";
import { createColumnDefs } from "@/utils/dataTable";
import { getPriceTypeTranslation } from "@/utils/translation";

const getColumns = (t: TFunction, currency: string, language: string) =>
  createColumnDefs<PriceAdjustment>(({ createData }) => [
    createData("id", {
      label: t("id"),
      display: "number",
      filterable: false,
    }),
    createData("status", {
      label: t("status"),
      filterKind: "autocomplete",
      renderAs: (originalValue) => ({
        type: "badge",
        label: t(originalValue),
        colors: {
          done: "green",
          pending: "gray",
          partial: "yellow",
        },
      }),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("type", {
      label: t("type"),
      filterKind: "autocomplete",
      renderAs: (originalValue) => ({
        type: "badge",
        label: t(originalValue.replace("_", " ")),
        colors: {
          service: "blue",
          product: "green",
          fixed_price: "yellow",
        },
      }),
      renderOptionsLabel: (value) => t(value.replace("_", " ")),
    }),
    createData("priceType", {
      label: t("price type"),
      filterKind: "autocomplete",
      renderAs: (originalValue) => ({
        type: "badge",
        label: getPriceTypeTranslation(originalValue),
        colors: {
          fixed_price_with_vat: "green",
          dynamic_percentage: "yellow",
          dynamic_fixed_with_vat: "blue",
        },
      }),
      renderOptionsLabel: (value) => getPriceTypeTranslation(value),
    }),
    createData("price", {
      label: t("price"),
      display: "number",
      render: (price, _, context) => {
        if (context.row.original.priceType !== "dynamic_percentage") {
          return formatCurrency(language, currency, price);
        }
        return `${price}%`;
      },
    }),
    createData("executionDate", {
      label: t("execution date"),
      display: "date",
      filterKind: "date",
    }),
    createData("createdAt", {
      label: t("created at"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("causer.fullname", {
      label: t("created by"),
    }),
  ]);

export default getColumns;
