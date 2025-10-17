import { TFunction } from "i18next";

import { OrderFixedPriceRow } from "@/types/orderFixedPrice";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<OrderFixedPriceRow>(({ createData }) => [
    createData("type", {
      label: t("description"),
      render: (originalValue) => t(originalValue),
    }),
    createData("priceWithVat", {
      label: t("price include vat"),
      display: "currency",
    }),
    createData("vatGroup", { label: t("vat") }),
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
