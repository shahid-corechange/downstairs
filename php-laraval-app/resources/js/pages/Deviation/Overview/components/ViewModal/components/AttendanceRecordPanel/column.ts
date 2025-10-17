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
    createData("status", {
      label: t("status"),
      filterKind: "autocomplete",
      renderAs: (originalValue) => ({
        type: "badge",
        label: t(originalValue),
        colors: {
          done: "green",
          cancel: "red",
          pending: "yellow",
          progress: "blue",
        },
      }),
      renderOptionsLabel: (value) => t(value),
    }),
  ]);

export default getColumns;
