import { TFunction } from "i18next";

import StoreSale from "@/types/storeSale";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<StoreSale>(({ createData }) => [
    createData("id", {
      label: t("id"),
      display: "number",
    }),
    createData("createdAt", {
      label: t("created at"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("roundedTotalToPay", {
      label: t("total price"),
      filterable: false,
      display: "currency",
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
          queryKey: ["web", "cashier", "direct-sales", "histories", "json"],
          select: (response) =>
            response.data.data.map((storeSale) => storeSale.status),
        },
      },
      renderAs: (originalValue) => ({
        type: "badge",
        label: t(originalValue),
        colors: {
          pending: "gray",
          paid: "green",
          cancelled: "red",
        },
      }),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("paymentMethod", {
      label: t("payment method"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["paymentMethod"],
          groupBy: "paymentMethod",
          sort: { status: "asc" },
        },
        query: {
          queryKey: ["web", "cashier", "direct-sales", "histories", "json"],
          select: (response) =>
            response.data.data.map((storeSale) => storeSale.paymentMethod),
        },
      },
      render: (originalValue) => t(originalValue),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("causer", {
      id: "causerId",
      label: t("handled by"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          include: ["causer"],
          only: ["causer.id", "causer.fullname"],
          groupBy: "causerId",
          sort: {
            "causer.fullname": "asc",
          },
        },
        query: {
          queryKey: ["web", "cashier", "direct-sales", "histories", "json"],
          select: (response) => response.data.data.map(({ causer }) => causer),
        },
      },
      render: (originalValue) => originalValue?.fullname ?? t("unknown"),
      renderOptionsLabel: (value) => value?.fullname ?? t("unknown"),
      getOptionsValue: (value) => value?.id ?? "null",
    }),
  ]);

export default getColumns;
