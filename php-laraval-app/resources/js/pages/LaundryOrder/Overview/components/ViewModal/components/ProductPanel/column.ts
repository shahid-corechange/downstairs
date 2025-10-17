import { TFunction } from "i18next";

import { LaundryOrderProduct } from "@/types/laundryOrderProduct";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<LaundryOrderProduct>(({ createData }) => [
    createData("name", {
      label: t("name"),
    }),
    createData("price", {
      label: t("price"),
      display: "currency",
    }),
    createData("quantity", {
      label: t("quantity"),
    }),
    createData("product.unit", {
      label: t("unit"),
    }),
    createData("product.vatGroup", { label: t("vat") }),
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
    createData("note", {
      label: t("note"),
      render: (value) => value ?? "-",
    }),
  ]);

export default getColumns;
