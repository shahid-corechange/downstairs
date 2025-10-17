import { TFunction } from "i18next";

import TimeReport from "@/types/timeReport";

import { createColumnDefs } from "@/utils/dataTable";
import { capitalizeEachWord } from "@/utils/string";
import { interpretTotalHours } from "@/utils/time";

const getColumns = (t: TFunction) =>
  createColumnDefs<TimeReport>(({ createData }) => [
    createData("id", {
      label: t("id"),
      display: "number",
      filterable: false,
    }),
    createData("user.fullname", {
      label: t("employee"),
      render: (value) => capitalizeEachWord(value),
    }),
    createData("user.employee.identityNumber", {
      label: t("identity number"),
      filterable: false,
    }),
    createData("type", {
      label: t("type"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["type"],
          groupBy: "type",
          sort: { type: "asc" },
        },
        query: {
          queryKey: ["web", "time-reports", "daily", "json"],
          select: (response) =>
            response.data.data.map(({ type }) => type ?? ""),
        },
      },
      render: (originalValue) => t(`time report type ${originalValue}`),
      renderOptionsLabel: (value) => t(`time report type ${value}`),
    }),
    createData("date", {
      label: t("date"),
      display: "date",
      filterKind: "date",
    }),
    createData("startTime", {
      label: t("start time"),
      filterKind: "time",
    }),
    createData("endTime", {
      label: t("end time"),
      filterKind: "time",
    }),
    createData("workHours", {
      label: t("work time"),
      display: "number",
      filterable: false,
      render: (value) => interpretTotalHours(value),
    }),
    createData("timeAdjustmentHours", {
      label: t("time adjustment"),
      display: "number",
      filterable: false,
      render: (value) => interpretTotalHours(value),
    }),
    createData("totalHours", {
      label: t("total time"),
      display: "number",
      filterable: false,
      render: (value) => interpretTotalHours(value),
    }),
    createData("bookingHours", {
      label: t("booking time"),
      display: "number",
      filterable: false,
      render: (value) => interpretTotalHours(value),
    }),
    createData("hasDeviation", {
      label: t("deviation"),
      display: "boolean",
      filterable: false,
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("yes") : t("no"),
        colorScheme: originalValue ? "red" : "green",
      }),
      renderOptionsLabel: (value) => (value ? t("yes") : t("no")),
    }),
  ]);

export default getColumns;
