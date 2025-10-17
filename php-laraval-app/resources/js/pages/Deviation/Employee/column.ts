import { TFunction } from "i18next";

import { DEVIATION_TYPES } from "@/constants/deviation";

import Deviation from "@/types/deviation";
import User from "@/types/user";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Deviation>(({ createData }) => [
    createData("id", {
      label: t("id"),
      display: "number",
      filterable: false,
    }),
    createData("user", {
      id: "user.id",
      label: t("employee"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          include: ["user"],
          only: ["user.id", "user.fullname"],
          groupBy: "userId",
          sort: { "user.fullname": "asc" },
        },
        query: {
          queryKey: ["web", "deviations", "employee", "json"],
          select: (response) => response.data.data.map(({ user }) => user),
        },
      },
      render: (originalValue) => originalValue?.fullname ?? "",
      renderOptions: (values) =>
        values
          .filter((user): user is User => user !== undefined)
          .map(({ id, fullname }) => ({ value: id, label: fullname })),
    }),
    createData("schedule.endAt", {
      label: t("schedule end"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("createdAt", {
      label: t("created at"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("type", {
      label: t("type"),
      filterKind: "autocomplete",
      options: [...DEVIATION_TYPES],
      render: (originalValue) => t(`deviation type ${originalValue}`),
      renderOptionsLabel: (value) => t(`deviation type ${value}`),
    }),
    createData("reason", {
      label: t("reason"),
      render: (originalValue) => t(originalValue),
    }),
    createData("isHandled", {
      label: t("is handled"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["isHandled"],
          groupBy: "isHandled",
        },
        query: {
          queryKey: ["web", "deviations", "employee", "json"],
          select: (response) =>
            response.data.data.map(({ isHandled }) => isHandled),
        },
      },
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("yes") : t("no"),
        colorScheme: originalValue ? "green" : "red",
      }),
      renderOptionsLabel: (value) => (value ? t("yes") : t("no")),
    }),
  ]);

export default getColumns;
