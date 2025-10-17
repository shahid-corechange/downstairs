import { TFunction } from "i18next";

import { ScheduleChangeRequest } from "@/types/schedule";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<ScheduleChangeRequest>(({ createData }) => [
    createData("id", {
      label: t("id"),
      display: "number",
      filterable: false,
    }),
    createData("schedule.user.fullname", {
      label: t("customer"),
    }),
    createData("schedule.property.address.fullAddress", {
      label: t("address"),
    }),
    createData("schedule.team.name", {
      label: t("team"),
    }),
    createData("schedule.startAt", {
      label: t("start at"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("startAtChanged", {
      label: t("request start at"),
      display: "datetime",
      filterKind: "date",
      render: (originalValue, value) =>
        originalValue ? value : t("no change requested"),
    }),
    createData("schedule.endAt", {
      label: t("end at"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("endAtChanged", {
      label: t("request end at"),
      display: "datetime",
      filterKind: "date",
      render: (originalValue, value) =>
        originalValue ? value : t("no change requested"),
    }),
    createData("canReschedule", {
      label: t("can reschedule"),
      display: "boolean",
      filterKind: "autocomplete",
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("yes") : t("no"),
        colorScheme: originalValue ? "green" : "red",
      }),
      renderOptionsLabel: (value) => (value ? t("yes") : t("no")),
    }),
  ]);

export default getColumns;
