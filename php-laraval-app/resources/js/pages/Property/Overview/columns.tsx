import { ListItem, UnorderedList } from "@chakra-ui/react";
import { TFunction } from "i18next";

import Property from "@/types/property";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Property>(({ createData, createAccessor }) => [
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
          queryKey: ["web", "customers", "properties", "json"],
          select: (response) => response.data.data.map(({ status }) => status),
        },
      },
      renderAs: (originalValue) => ({
        type: "badge",
        label: t(originalValue),
        colors: {
          active: "green",
          inactive: "red",
        },
      }),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("address.fullAddress", {
      label: t("address"),
    }),
    createData("users", {
      id: "users.id",
      label: t("customer"),
      display: "list",
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          include: ["users"],
          only: ["users.id", "users.fullname"],
          sort: { "users.fullname": "asc" },
        },
        query: {
          queryKey: ["web", "customers", "properties", "json"],
          select: (response) => response.data.data.map(({ users }) => users),
        },
      },
      render: (originalvalue) => (
        <UnorderedList>
          {originalvalue?.map((user) => (
            <ListItem key={user.id}>{user.fullname}</ListItem>
          ))}
        </UnorderedList>
      ),
      renderOptionsLabel: (user) => user?.fullname ?? "",
      getOptionsValue: (user) => user?.id ?? "",
    }),
    createData("type", {
      id: "type.id",
      label: t("property type"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          include: ["type"],
          only: ["type.id", "type.name"],
          groupBy: "typeId",
          sort: { "type.name": "asc" },
        },
        query: {
          queryKey: ["web", "customers", "properties", "json"],
          select: (response) => response.data.data.map(({ type }) => type),
        },
      },
      render: (originalValue) => originalValue?.name ?? "",
      renderOptionsLabel: (value) => value?.name ?? "",
      getOptionsValue: (value) => value?.id ?? "",
    }),
    createData("squareMeter", {
      label: t("square meter"),
      display: "number",
    }),
    createData("keyDescription", {
      label: t("key information"),
      filterable: false,
      render: (originalvalue) => originalvalue || "-",
    }),
    createData("keyInformation.keyPlace", {
      label: t("key place"),
      render: (originalvalue) => originalvalue || "-",
    }),
    createAccessor("meta.note", {
      label: t("note"),
      getValue: (property) => property.meta?.note,
      render: (originalvalue) => originalvalue || "-",
    }),
  ]);

export default getColumns;
