import { TFunction } from "i18next";

import { PriceAdjustmentRow } from "@/types/priceAdjustment";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<PriceAdjustmentRow>(({ createData }) => [
    createData("id", {
      label: t("id"),
    }),
    createData("adjustableName", {
      label: t("name"),
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
        },
      }),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("previousPrice", {
      label: t("previous price"),
      display: "currency",
    }),
    createData("priceWithVat", {
      label: t("price"),
      display: "currency",
    }),
  ]);

export default getColumns;
