import { TFunction } from "i18next";

import { MONTHS } from "@/constants/datetime";

import MonthlyTimeReport from "@/types/monthlyTimeReport";

import { createColumnDefs } from "@/utils/dataTable";
import { capitalizeString } from "@/utils/string";
import { interpretTotalHours } from "@/utils/time";

const getColumns = (t: TFunction) =>
  createColumnDefs<MonthlyTimeReport>(({ createData }) => [
    createData("userId", {
      label: t("employee id"),
      display: "number",
      filterable: false,
    }),
    createData("fortnoxId", {
      label: t("fortnox employee id"),
      display: "number",
      render: (value) => (value ? capitalizeString(value) : "-"),
    }),
    createData("employee.identityNumber", {
      label: t("identity number"),
      filterable: false,
    }),
    createData("fullname", {
      label: t("employee"),
      render: (value) => capitalizeString(value),
    }),
    createData("month", {
      label: t("month"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          size: -1,
          only: ["month"],
          groupBy: "month",
          sort: { month: "asc" },
        },
        query: {
          queryKey: ["web", "time-reports", "json"],
          select: (response) => response.data.data.map(({ month }) => month),
        },
      },
      render: (value) => t(MONTHS[value - 1]),
      renderOptionsLabel: (value) => t(MONTHS[value - 1]),
    }),
    createData("year", {
      label: t("year"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          size: -1,
          only: ["year"],
          groupBy: "year",
          sort: { year: "asc" },
        },
        query: {
          queryKey: ["web", "time-reports", "json"],
          select: (response) => response.data.data.map(({ year }) => year),
        },
      },
    }),
    createData("totalWorkHours", {
      label: t("work time"),
      display: "number",
      filterable: false,
      render: (value) => interpretTotalHours(value),
    }),
    createData("adjustmentHours", {
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
