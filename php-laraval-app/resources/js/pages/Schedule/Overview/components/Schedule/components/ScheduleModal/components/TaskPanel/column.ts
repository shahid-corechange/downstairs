import { TFunction } from "i18next";

import { ScheduleTask } from "@/types/schedule";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<ScheduleTask>(({ createData }) => [
    createData("name", {
      label: t("name"),
    }),
    createData("description", {
      label: t("description"),
    }),
    createData("source", {
      label: t("type"),
      render: (value) =>
        value === "schedule" ? t("custom") : value ? t(value) : "-",
    }),
  ]);

export default getColumns;
