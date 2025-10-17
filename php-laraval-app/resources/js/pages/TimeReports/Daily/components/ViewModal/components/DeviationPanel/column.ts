import { TFunction } from "i18next";

import Deviation from "@/types/deviation";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Deviation>(({ createData }) => [
    createData("schedule.startAt", {
      label: t("start at"),
      display: "datetime",
      filterable: false,
    }),
    createData("schedule.endAt", {
      label: t("end at"),
      display: "datetime",
      filterable: false,
    }),
    createData("schedule.property.address.fullAddress", {
      label: t("address"),
    }),
    createData("type", {
      label: t("type"),
      render: (originalValue) => t(`deviation type ${originalValue}`),
    }),
    createData("reason", {
      label: t("reason"),
      sortable: false,
    }),
    createData("isHandled", {
      label: t("is handled"),
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
