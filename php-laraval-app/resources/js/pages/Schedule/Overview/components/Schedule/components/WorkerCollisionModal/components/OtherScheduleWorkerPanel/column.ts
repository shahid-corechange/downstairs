import { TFunction } from "i18next";

import ScheduleEmployee from "@/types/scheduleEmployee";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<ScheduleEmployee>(({ createData, createAccessor }) => [
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
    createData("user.fullname", {
      label: t("worker"),
    }),
    createAccessor("type", {
      label: t("type"),
      getValue: (scheduleEmployee) =>
        (scheduleEmployee.schedule?.team?.users ?? []).findIndex(
          (user) => user.id === scheduleEmployee.userId,
        ) > -1,
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("base") : t("temporary"),
        colorScheme: originalValue ? "green" : "blue",
      }),
    }),
  ]);

export default getColumns;
