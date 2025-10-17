import { TFunction } from "i18next";

import { FIXED_PRICE_TYPES } from "@/constants/fixedPrice";

import FixedPrice from "@/types/fixedPrice";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<FixedPrice>(({ createData }) => [
    createData("id", {
      label: t("id"),
      display: "number",
      filterable: false,
    }),
    createData("isActive", {
      label: t("status"),
      options: [
        { label: t("active"), value: true },
        { label: t("inactive"), value: false },
      ],
      filterKind: "autocomplete",
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("active") : t("inactive"),
        colorScheme: originalValue ? "green" : "red",
      }),
    }),
    createData("user", {
      id: "userId",
      label: t("company"),
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
          queryKey: ["web", "companies", "fixedprices", "json"],
          select: (response) =>
            response.data.data.map((fixedprice) => fixedprice.user),
        },
      },
      render: (originalValue) => originalValue?.fullname ?? "",
      renderOptionsLabel: (value) => value?.fullname ?? "",
      getOptionsValue: (value) => value?.id ?? "",
    }),
    createData("hasActiveSubscriptions", {
      label: t("has bookings"),
      filterKind: "autocomplete",
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("yes") : t("no"),
        colorScheme: originalValue ? "green" : "red",
      }),
      renderOptionsLabel: (value) => (value ? t("yes") : t("no")),
    }),
    createData("type", {
      label: t("type"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["type"],
          groupBy: "type",
        },
        query: {
          queryKey: ["web", "customers", "fixedprices", "json"],
          select: (response) => response.data.data.map(({ type }) => type),
        },
      },
      renderAs: (originalValue: (typeof FIXED_PRICE_TYPES)[number]) => ({
        type: "badge",
        label: t(originalValue ?? "-"),
        colors: {
          "cleaning": "blue",
          "laundry": "orange",
          "cleaning and laundry": "green",
        },
      }),
      renderOptionsLabel: (value) => t(value ?? "-"),
    }),
    createData("isPerOrder", {
      label: t("period"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["isPerOrder"],
          groupBy: "isPerOrder",
        },
        query: {
          queryKey: ["web", "companies", "fixedprices", "json"],
          select: (response) =>
            response.data.data.map(({ isPerOrder }) => isPerOrder),
        },
      },
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("per booking") : t("monthly"),
        colorScheme: originalValue ? "blue" : "orange",
      }),
      renderOptionsLabel: (value) => (value ? t("per booking") : t("monthly")),
    }),
    createData("startDate", {
      label: t("start date"),
      display: "date",
      filterKind: "date",
    }),
    createData("endDate", {
      label: t("end date"),
      display: "date",
      filterKind: "date",
    }),
  ]);

export default getColumns;
