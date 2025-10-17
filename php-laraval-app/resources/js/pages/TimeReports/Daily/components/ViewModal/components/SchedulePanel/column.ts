import { TFunction } from "i18next";

import Schedule from "@/types/schedule";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Schedule>(({ createData }) => [
    createData("customer.name", {
      label: t("customer"),
      filterKind: "autocomplete",
    }),
    createData("startAt", {
      label: t("start at"),
      display: "datetime",
      filterable: false,
    }),
    createData("endAt", {
      label: t("end at"),
      display: "datetime",
      filterable: false,
    }),
    createData("property.address.fullAddress", {
      label: t("address"),
    }),
    createData("hasDeviation", {
      label: t("deviation"),
      display: "boolean",
      filterable: false,
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("yes") : t("no"),
        colorScheme: originalValue ? "red" : "green",
      }),
      renderOptionsLabel: (value) => (value ? t("yes") : t("no")),
    }),
  ]);

export default getColumns;
