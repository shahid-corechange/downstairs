import { TFunction } from "i18next";

import Customer from "@/types/customer";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Customer>(({ createData, createAccessor }) => [
    createData("id", {
      label: t("id"),
      display: "number",
      filterable: false,
    }),
    createAccessor("deletedAt", {
      label: t("status"),
      options: [
        { label: t("active"), value: false },
        { label: t("inactive"), value: true },
      ],
      filterKind: "autocomplete",
      filterCriteria: (value) => (value === "true" ? "neq" : "eq"),
      filterValueTransformer: () => "null",
      getValue: (company) => !!company.deletedAt,
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("deleted") : t("active"),
        colorScheme: originalValue ? "red" : "green",
      }),
    }),
    createData("name", {
      label: t("company name"),
    }),
    createData("email", {
      label: t("email"),
    }),
    createData("formattedPhone1", {
      id: "phone1",
      label: t("phone"),
      display: "phone",
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
