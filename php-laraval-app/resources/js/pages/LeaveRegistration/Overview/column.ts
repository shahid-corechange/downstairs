import { TFunction } from "i18next";

import LeaveRegistration from "@/types/leaveRegistration";

import { createColumnDefs } from "@/utils/dataTable";
import { capitalizeString } from "@/utils/string";

const getColumns = (t: TFunction) =>
  createColumnDefs<LeaveRegistration>(({ createData }) => [
    createData("id", {
      label: t("id"),
      display: "number",
      filterable: false,
    }),
    createData("isStopped", {
      label: t("stopped"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["isStopped"],
          sort: { isStopped: "asc" },
          groupBy: "isStopped",
        },
        query: {
          queryKey: ["web", "leave-registrations", "json"],
          select: (response) =>
            response.data.data.map(({ isStopped }) => isStopped),
        },
      },
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("yes") : t("no"),
        colorScheme: originalValue ? "red" : "green",
      }),
      renderOptionsLabel: (value) => (value ? t("yes") : t("no")),
    }),
    createData("employee.userId", {
      label: t("employee id"),
      display: "number",
    }),
    createData("employee.name", {
      label: t("employee name"),
      render: (value) => capitalizeString(value),
    }),
    createData("type", {
      label: t("type"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["type"],
          sort: { type: "asc" },
          groupBy: "type",
        },
        query: {
          queryKey: ["web", "leave-registrations", "json"],
          select: (response) => response.data.data.map(({ type }) => type),
        },
      },
      render: (value) => t(value),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("startAt", {
      label: t("start date"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("endAt", {
      label: t("stop date"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("rescheduleNeeded", {
      label: t("rescheduling needed"),
      filterKind: "autocomplete",
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("yes") : t("no"),
        colorScheme: originalValue ? "red" : "green",
      }),
      renderOptionsLabel: (value) => (value ? t("yes") : t("no")),
    }),
  ]);

export default getColumns;
