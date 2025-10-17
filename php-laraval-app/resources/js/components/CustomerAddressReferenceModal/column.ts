import { TFunction } from "i18next";

import Customer from "@/types/customer";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Customer>(({ createData, createAccessor }) => [
    createAccessor("deletedAt", {
      label: t("status"),
      getValue: (customer) => !!customer.deletedAt,
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("inactive") : t("active"),
        colorScheme: originalValue ? "red" : "green",
      }),
    }),
    createData("name", {
      label: t("name"),
    }),
    createData("reference", {
      label: t("customer reference"),
    }),
    createData("address.fullAddress", {
      label: t("address"),
    }),
    createData("identityNumber", {
      label: t("identity number"),
    }),
    createData("formattedPhone1", {
      label: t("phone"),
    }),
    createData("email", {
      label: t("email"),
    }),
    createData("dueDays", {
      label: t("invoice due days"),
    }),
    createData("invoiceMethod", {
      label: t("send invoice method"),
      renderAs: (originalValue) => ({
        type: "badge",
        label: t(originalValue),
        colors: {
          email: "blue",
          print: "orange",
        },
      }),
    }),
  ]);

export default getColumns;
