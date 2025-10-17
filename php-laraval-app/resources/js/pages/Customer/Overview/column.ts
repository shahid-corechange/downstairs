import { TFunction } from "i18next";

import locales from "@/data/locales.json";

import User from "@/types/user";

import { createColumnDefs } from "@/utils/dataTable";
import { capitalizeEachWord } from "@/utils/string";

const getColumns = (t: TFunction) =>
  createColumnDefs<User>(({ createData }) => [
    createData("id", {
      label: t("id"),
      display: "number",
      filterable: false,
    }),
    createData("status", {
      label: t("status"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          include: ["status"],
          only: ["status"],
          groupBy: "status",
          sort: { status: "asc" },
        },
        query: {
          queryKey: ["web", "customers", "json"],
          select: (response) => response.data.data.map(({ status }) => status),
        },
      },
      renderAs: (originalValue) => ({
        type: "badge",
        label: t(originalValue),
        colors: {
          active: "green",
          inactive: "red",
          suspended: "red",
          deleted: "red",
          pending: "yellow",
          blocked: "red",
        },
      }),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("firstName", {
      label: t("first name"),
      render: (value) => capitalizeEachWord(value),
    }),
    createData("lastName", {
      label: t("last name"),
      render: (value) => capitalizeEachWord(value) || "-",
    }),
    createData("email", {
      label: t("email"),
    }),
    createData("formattedCellphone", {
      id: "cellphone",
      label: t("phone"),
      display: "phone",
    }),
    createData("info.timezone", {
      label: t("timezone"),
    }),
    createData("info.language", {
      label: t("language"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          include: ["info"],
          only: ["info.language"],
          sort: { "info.language": "asc" },
        },
        query: {
          queryKey: ["web", "customers", "json"],
          select: (response) =>
            response.data.data.map(({ info }) => info?.language ?? ""),
        },
      },
      renderOptionsLabel: (value) =>
        locales.find((locale) => locale.value === value)?.label || value,
      render: (originalValue) =>
        locales.find((locale) => locale.value === originalValue)?.label,
    }),
    createData("createdAt", {
      label: t("created at"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("updatedAt", {
      label: t("updated at"),
      display: "datetime",
      filterKind: "date",
    }),
  ]);

export default getColumns;
