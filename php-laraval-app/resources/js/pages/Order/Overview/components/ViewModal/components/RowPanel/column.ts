import { TFunction } from "i18next";

import { OrderRow } from "@/types/order";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<OrderRow>(({ createData, createAccessor }) => [
    createData("description", {
      label: t("description"),
    }),
    createData("priceWithVat", {
      label: t("price"),
      display: "currency",
    }),
    createData("quantity", {
      label: t("quantity"),
    }),
    createData("unit", {
      label: t("unit"),
    }),
    createData("vat", { label: t("vat") }),
    createData("hasRut", {
      label: t("rut"),
      display: "boolean",
      filterKind: "autocomplete",
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("yes") : t("no"),
        colorScheme: originalValue ? "green" : "red",
      }),
      renderOptionsLabel: (value) => (value ? t("yes") : t("no")),
    }),
    createData("discountPercentage", {
      label: t("discount percentage"),
      render: (value) => `${value}%` ?? 0,
    }),
    createAccessor("total", {
      label: t("total"),
      display: "currency",
      getValue: (value) => {
        const totalPrice = value.priceWithVat * value.quantity;
        const discount = totalPrice * ((value.discountPercentage ?? 0) / 100);
        return totalPrice - discount;
      },
    }),
    createData("internalNote", {
      label: t("internal note"),
      render: (value) => value ?? "-",
    }),
  ]);

export default getColumns;
