import { TFunction } from "i18next";

import Credit from "@/types/credit";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Credit>(({ createData }) => [
    createData("type", {
      label: t("type"),
      filterKind: "autocomplete",
      renderAs: (originalValue) => ({
        type: "badge",
        label: t(originalValue),
        colors: {
          refund: "blue",
          granted: "green",
        },
      }),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("remainingAmount", {
      label: t("amount"),
      display: "number",
      filterable: false,
    }),
    createData("description", {
      label: t("description"),
    }),
    createData("validUntil", {
      label: t("expiration date"),
      display: "date",
      filterKind: "date",
    }),
    createData("issuer", {
      id: "issuerId",
      label: t("issuer"),
      filterKind: "autocomplete",
      render: (originalValue) => originalValue?.fullname ?? t("system"),
      renderOptionsLabel: (issuer) => issuer?.fullname ?? t("system"),
      getOptionsValue: (value) => value?.id ?? "null",
    }),
  ]);

export default getColumns;
