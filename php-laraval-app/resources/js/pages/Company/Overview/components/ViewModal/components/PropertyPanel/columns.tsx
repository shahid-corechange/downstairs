import { TFunction } from "i18next";

import Property from "@/types/property";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction, userId: string) =>
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
          filter: {
            eq: {
              membershipType: "company",
            },
          },
          groupBy: "status",
          sort: { status: "asc" },
        },
        query: {
          queryKey: ["web", "customers", userId, "properties"],
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
          filter: {
            eq: {
              membershipType: "company",
            },
          },
          groupBy: "typeId",
          sort: { "type.name": "asc" },
        },
        query: {
          queryKey: ["web", "customers", userId, "properties"],
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
