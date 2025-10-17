import { TFunction } from "i18next";

import Order from "@/types/order";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Order>(({ createData }) => [
    createData("id", {
      label: t("id"),
      display: "number",
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
          queryKey: ["web", "orders", "json"],
          select: (response) => response.data.data.map(({ user }) => user),
        },
      },
      render: (originalValue) => originalValue?.fullname ?? "",
      renderOptionsLabel: (value) => value?.fullname ?? "",
      getOptionsValue: (value) => value?.id ?? "",
    }),
    createData("customer.membershipType", {
      label: t("type"),
      renderAs: (originalValue) => ({
        type: "badge",
        label: t(originalValue),
        colors: {
          company: "blue",
          private: "orange",
        },
      }),
      filterKind: "autocomplete",
      renderOptionsLabel: (value) => t(value),
    }),
    createData("customer.address.fullAddress", {
      label: t("address"),
    }),
    createData("paidBy", {
      label: t("paid by"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["paidBy"],
          groupBy: "paidBy",
          sort: { paidBy: "asc" },
        },
        query: {
          queryKey: ["web", "orders", "json"],
          select: (response) => response.data.data.map(({ paidBy }) => paidBy),
        },
      },
      render: (originalValue) => t(originalValue),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("paidAt", {
      label: t("paid at"),
      display: "datetime",
    }),
    createData("orderedAt", {
      label: t("date"),
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
          queryKey: ["web", "orders", "json"],
          select: (response) => response.data.data.map(({ status }) => status),
        },
      },
      renderAs: (originalValue) => ({
        type: "badge",
        label: t(`order status ${originalValue}`),
        colors: {
          done: "green",
          cancel: "red",
          draft: "gray",
          progress: "orange",
        },
      }),
      renderOptionsLabel: (value) => t(`order status ${value}`),
    }),
  ]);

export default getColumns;
