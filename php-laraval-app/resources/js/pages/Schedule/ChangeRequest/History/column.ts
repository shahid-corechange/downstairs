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
    createData("originalStartAt", {
      label: t("original start at"),
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
    createData("originalEndAt", {
      label: t("original end at"),
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
    createData("status", {
      label: t("status"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          only: ["status"],
          size: -1,
          show: "all",
          sort: {
            status: "asc",
          },
        },
        query: {
          queryKey: [
            "web",
            "schedules",
            "change-requests",
            "histories",
            "json",
          ],
          select: (response) => response.data.data.map(({ status }) => status),
        },
      },
      renderAs: (originalValue) => ({
        type: "badge",
        label: t(originalValue),
        colors: {
          approved: "green",
          rejected: "red",
          handled: "blue",
          canceled: "orange",
        },
      }),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("causer", {
      id: "causerId",
      label: t("causer"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          include: ["causer"],
          only: ["causer.id", "causer.fullname"],
          groupBy: "causerId",
          sort: {
            "causer.fullname": "asc",
          },
        },
        query: {
          queryKey: [
            "web",
            "schedules",
            "change-requests",
            "histories",
            "json",
          ],
          select: (response) => response.data.data.map(({ causer }) => causer),
        },
      },
      render: (originalValue) => originalValue?.fullname ?? t("unknown"),
      renderOptionsLabel: (value) => value?.fullname ?? t("unknown"),
      getOptionsValue: (value) => value?.id ?? "null",
    }),
  ]);

export default getColumns;
