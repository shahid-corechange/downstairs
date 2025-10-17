import { TFunction } from "i18next";

import { LaundryOrderSchedule } from "@/types/laundryOrderSchedule";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<LaundryOrderSchedule>(({ createData }) => [
    createData("type", {
      label: t("type"),
    }),
    createData("date", {
      label: t("date"),
      display: "date",
    }),
    createData("time", {
      label: t("time"),
    }),
  ]);

export default getColumns;
