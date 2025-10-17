import { TFunction } from "i18next";

import Deviation from "@/types/deviation";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Deviation>(({ createData }) => [
    createData("user.fullname", {
      label: t("employee"),
      filterable: false,
    }),
    createData("schedule.endAt", {
      label: t("schedule end"),
      filterable: false,
      display: "datetime",
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
      sortable: false,
      autocompleteFreeMode: false,
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("yes") : t("no"),
        colorScheme: originalValue ? "green" : "red",
      }),
      renderOptionsLabel: (value) => (value ? t("yes") : t("no")),
    }),
  ]);

export default getColumns;
