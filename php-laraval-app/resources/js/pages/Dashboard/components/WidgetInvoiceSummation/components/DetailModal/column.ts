import { TFunction } from "i18next";

import InvoiceSummation from "@/types/invoiceSummation";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<InvoiceSummation>(({ createData }) => [
    createData("invoicePeriod", {
      label: t("invoice period"),
      filterKind: "autocomplete",
    }),
    createData("sentPeriod", {
      label: t("sent period"),
      filterKind: "autocomplete",
    }),
    createData("totalGross", {
      label: t("total gross"),
      display: "currency",
      cellOptions: (value) => ({
        color: value >= 0 ? "green.500" : "red.500",
        fontWeight: "bold",
        _dark: {
          color: value >= 0 ? "green.300" : "red.300",
        },
      }),
    }),
    createData("totalNet", {
      label: t("total net"),
      display: "currency",
      cellOptions: (value) => ({
        color: value >= 0 ? "green.500" : "red.500",
        fontWeight: "bold",
        _dark: {
          color: value >= 0 ? "green.300" : "red.300",
        },
      }),
    }),
    createData("totalVat", {
      label: t("total vat"),
      display: "currency",
      cellOptions: (value) => ({
        color: value >= 0 ? "green.500" : "red.500",
        fontWeight: "bold",
        _dark: {
          color: value >= 0 ? "green.300" : "red.300",
        },
      }),
    }),
    createData("totalIncludeVat", {
      label: t("total include vat"),
      display: "currency",
      cellOptions: (value) => ({
        color: value >= 0 ? "green.500" : "red.500",
        fontWeight: "bold",
        _dark: {
          color: value >= 0 ? "green.300" : "red.300",
        },
      }),
    }),
    createData("totalRut", {
      label: t("total rut"),
      display: "currency",
      cellOptions: (value) => ({
        color: value >= 0 ? "green.500" : "red.500",
        fontWeight: "bold",
        _dark: {
          color: value >= 0 ? "green.300" : "red.300",
        },
      }),
    }),
    createData("totalInvoiced", {
      label: t("total invoiced"),
      display: "currency",
      cellOptions: (value) => ({
        color: value >= 0 ? "green.500" : "red.500",
        fontWeight: "bold",
        _dark: {
          color: value >= 0 ? "green.300" : "red.300",
        },
      }),
    }),
    createData("invoiceCount", {
      label: t("total invoices"),
      display: "number",
    }),
  ]);

export default getColumns;
