import { TFunction } from "i18next";

import { LaundryOrder } from "@/types/laundryOrder";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<LaundryOrder>(({ createData }) => [
    createData("id", {
      label: t("id"),
      display: "number",
    }),
    createData("createdAt", {
      label: t("ordered at"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("user.fullname", {
      label: t("customer"),
      filterKind: "autocomplete",
    }),
    createData("store.name", {
      label: t("store"),
      filterKind: "autocomplete",
    }),
    createData("pickupInCleaning.startAt", {
      label: t("pickup at"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("deliveryInCleaning.startAt", {
      label: t("delivery at"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("status", {
      label: t("status"),
      filterKind: "autocomplete",
      renderAs: (originalValue) => ({
        type: "badge",
        label: t(originalValue),
        colors: {
          pending: "gray",
          picked_up: "gray",
          in_progress_pickup: "gray",
          in_progress_store: "gray",
          in_progress_laundry: "gray",
          in_progress_delivery: "gray",
          delivered: "orange",
          paid: "brand",
          done: "blue",
          closed: "green",
        },
      }),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("paidAt", {
      label: t("paid at"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("paymentMethod", {
      label: t("payment method"),
      filterKind: "autocomplete",
      render: (originalValue) => (originalValue ? t(originalValue) : "-"),
      renderOptionsLabel: (value) => (value ? t(value) : "-"),
    }),
  ]);

export default getColumns;
