import { TFunction } from "i18next";

import { MONTHS } from "@/constants/datetime";

import Invoice from "@/types/invoice";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Invoice>(({ createData }) => [
    createData("id", {
      label: t("id"),
      display: "number",
      filterable: false,
    }),
    createData("user", {
      id: "userId",
      label: t("customer"),
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
          queryKey: ["web", "invoices", "json"],
          select: (response) => response.data.data.map(({ user }) => user),
        },
      },
      render: (originalValue) => originalValue?.fullname,
      renderOptionsLabel: (value) => value?.fullname ?? "",
      getOptionsValue: (value) => value?.id ?? "",
    }),
    createData("customer.membershipType", {
      label: t("customer type"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          include: ["customer"],
          only: ["customer.membershipType"],
          groupBy: "customerId",
          sort: { "customer.membershipType": "asc" },
        },
        query: {
          queryKey: ["web", "invoices", "json"],
          select: (response) =>
            response.data.data.map(
              ({ customer }) => customer?.membershipType ?? "private",
            ),
        },
      },
      renderAs: (originalValue) => ({
        type: "badge",
        label: t(originalValue),
        colors: {
          company: "blue",
          private: "orange",
        },
      }),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("fortnoxInvoiceId", {
      label: t("fortnox invoice id"),
      render: (originalvalue) => originalvalue || "-",
    }),
    createData("type", {
      label: t("invoice type"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["type"],
          groupBy: "type",
          sort: { type: "asc" },
        },
        query: {
          queryKey: ["web", "invoices", "json"],
          select: (response) =>
            response.data.data.map(({ type }) => type ?? ""),
        },
      },
      render: (originalValue) => t(originalValue),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("category", {
      label: t("invoice category"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["category"],
          groupBy: "category",
          sort: { category: "asc" },
        },
        query: {
          queryKey: ["web", "invoices", "json"],
          select: (response) =>
            response.data.data.map(({ category }) => category ?? ""),
        },
      },
      render: (originalValue) => t(originalValue),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("month", {
      label: t("month"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["month"],
          groupBy: "month",
          sort: { month: "asc" },
        },
        query: {
          queryKey: ["web", "invoices", "json"],
          select: (response) => response.data.data.map(({ month }) => month),
        },
      },
      render: (value) => t(MONTHS[value - 1]),
      renderOptionsLabel: (value) => t(MONTHS[value - 1]),
    }),
    createData("year", {
      label: t("year"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["year"],
          groupBy: "year",
          sort: { year: "asc" },
        },
        query: {
          queryKey: ["web", "invoices", "json"],
          select: (response) => response.data.data.map(({ year }) => year),
        },
      },
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
      sortable: false,
      filterable: false,
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
      sortable: false,
      filterable: false,
    }),
    createData("sentAt", {
      label: t("sent date"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("dueAt", {
      label: t("due date"),
      display: "datetime",
      filterKind: "date",
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
          queryKey: ["web", "invoices", "json"],
          select: (response) => response.data.data.map(({ status }) => status),
        },
      },
      renderAs: (originalValue) => ({
        type: "badge",
        label: t(`invoice status ${originalValue}`),
        colors: {
          open: "gray",
          created: "orange",
          sent: "blue",
          cancel: "red",
          paid: "green",
        },
      }),
      renderOptionsLabel: (value) => t(`invoice status ${value}`),
    }),
  ]);

export default getColumns;
