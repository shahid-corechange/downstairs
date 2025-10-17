import { TFunction } from "i18next";

import ScheduleEmployee from "@/types/scheduleEmployee";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<ScheduleEmployee>(({ createData, createAccessor }) => [
    createData("user.fullname", {
      label: t("worker"),
    }),
    createAccessor("type", {
      label: t("type"),
      getValue: (scheduleEmployee) =>
        (scheduleEmployee?.schedule?.team?.users ?? []).findIndex(
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
