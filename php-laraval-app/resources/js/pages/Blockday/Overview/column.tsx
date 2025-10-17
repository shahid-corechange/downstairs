import { TFunction } from "i18next";

import Schedule from "@/types/schedule";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Schedule>(({ createData }) => [
    createData("id", {
      label: t("id"),
      filterable: false,
    }),
    createData("team.name", {
      label: t("team"),
      filterable: false,
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
  ]);

export default getColumns;
