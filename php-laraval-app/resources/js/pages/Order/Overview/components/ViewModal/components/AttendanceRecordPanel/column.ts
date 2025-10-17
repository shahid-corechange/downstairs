import { TFunction } from "i18next";

import ScheduleEmployee from "@/types/scheduleEmployee";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<ScheduleEmployee>(({ createData }) => [
    createData("user.fullname", {
      label: t("employee"),
      filterable: false,
    }),
    createData("startAt", {
      label: t("schedule start"),
      filterable: false,
      display: "datetime",
    }),
    createData("endAt", {
      label: t("schedule end"),
      filterable: false,
      display: "datetime",
    }),
  ]);

export default getColumns;
