import { ListItem, UnorderedList } from "@chakra-ui/react";
import { TFunction } from "i18next";

import locales from "@/data/locales.json";

import { Role } from "@/types/authorization";
import User from "@/types/user";

import { createColumnDefs } from "@/utils/dataTable";
import { capitalizeString } from "@/utils/string";

const getColumns = (t: TFunction) =>
  createColumnDefs<User>(({ createData, createAccessor }) => [
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
          only: ["status"],
          groupBy: "status",
          sort: { status: "asc" },
        },
        query: {
          queryKey: ["web", "employees", "json"],
          select: (response) => response.data.data.map((user) => user.status),
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
    createAccessor("employee.isValidIdentity", {
      label: t("identity status"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          include: ["employee"],
          only: ["employee.isValidIdentity"],
        },
        query: {
          queryKey: ["web", "employees", "json"],
          select: (response) =>
            response.data.data.map((user) => user.employee?.isValidIdentity),
        },
      },
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("valid") : t("invalid"),
        colorScheme: originalValue ? "green" : "red",
      }),
      renderOptionsLabel: (value) => (value ? t("valid") : t("invalid")),
      getValue: (user) => user.employee?.isValidIdentity,
    }),
    createData("firstName", {
      label: t("first name"),
      render: (value) => capitalizeString(value),
    }),
    createData("lastName", {
      label: t("last name"),
      render: (value) => capitalizeString(value),
    }),
    createData("email", {
      label: t("email"),
    }),
    createData("formattedCellphone", {
      id: "cellphone",
      label: t("phone"),
      display: "phone",
    }),
    createData("roles", {
      id: "roles.id",
      label: t("roles"),
      display: "list",
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          include: ["roles"],
          only: ["roles.id", "roles.name"],
          sort: { "roles.name": "asc" },
        },
        query: {
          queryKey: ["web", "employees", "json"],
          select: (response) => response.data.data.map((user) => user.roles),
        },
      },
      sortable: false,
      render: (originalValue) => (
        <UnorderedList>
          {originalValue
            ?.filter((role): role is Role => role !== undefined)
            .map((role) => <ListItem key={role.id}>{role.name}</ListItem>)}
        </UnorderedList>
      ),
      renderOptionsLabel: (value) => value?.name ?? "",
      getOptionsValue: (value) => value?.id ?? "",
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
          queryKey: ["web", "employees", "json"],
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
