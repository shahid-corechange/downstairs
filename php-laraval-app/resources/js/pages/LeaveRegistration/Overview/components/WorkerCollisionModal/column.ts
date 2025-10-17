import { TFunction } from "i18next";

import ScheduleEmployee from "@/types/scheduleEmployee";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<ScheduleEmployee>(({ createData }) => [
    createData("scheduleId", {
      label: t("id"),
      display: "number",
      filterable: false,
    }),
    createData("schedule.user.fullname", {
      label: t("customer"),
      filterKind: "autocomplete",
    }),
    createData("schedule.team.name", {
      label: t("team"),
      filterKind: "autocomplete",
    }),
    createData("schedule.startAt", {
      label: t("start at"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("schedule.endAt", {
      label: t("end at"),
      display: "datetime",
      filterKind: "date",
    }),
  ]);

export default getColumns;
