import { TFunction } from "i18next";

import { FixedPriceRow } from "@/types/fixedPrice";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<FixedPriceRow>(({ createData }) => [
    createData("type", {
      label: t("type"),
      filterKind: "autocomplete",
      render: (originalValue) => t(originalValue),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("priceWithVat", {
      label: t("price include vat"),
      display: "currency",
    }),
    createData("vatGroup", { label: t("vat"), display: "number" }),
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
  ]);

export default getColumns;
