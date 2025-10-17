import { TFunction } from "i18next";

import { ScheduleTask } from "@/types/schedule";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<ScheduleTask>(({ createData }) => [
    createData("name", {
      label: t("name"),
      filterable: false,
    }),
    createData("description", {
      label: t("description"),
      filterable: false,
    }),
    createData("isCompleted", {
      label: t("status"),
      display: "boolean",
      filterKind: "autocomplete",
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("completed") : t("not completed"),
        colorScheme: originalValue ? "green" : "red",
      }),
      renderOptionsLabel: (value) =>
        value ? t("completed") : t("not completed"),
    }),
  ]);

export default getColumns;
