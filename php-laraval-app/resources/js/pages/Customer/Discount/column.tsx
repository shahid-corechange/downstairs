import { TFunction } from "i18next";

import CustomerDiscount from "@/types/customerDiscount";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<CustomerDiscount>(({ createData }) => [
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
          queryKey: ["web", "customers", "discounts", "json"],
          select: (response) =>
            response.data.data.map((discount) => discount.user),
        },
      },
      render: (originalValue) => originalValue?.fullname ?? "",
      renderOptionsLabel: (value) => value?.fullname ?? "",
      getOptionsValue: (value) => value?.id ?? "",
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
          sort: { type: "asc" },
        },
        query: {
          queryKey: ["web", "customers", "discounts", "json"],
          select: (response) =>
            response.data.data.map((discount) => discount.type),
        },
      },
      render: (originalValue) => t(originalValue),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("value", {
      label: t("discount percentage"),
      display: "number",
      render: (value) => `${value}%`,
    }),
    createData("usageLimit", {
      label: t("usage limit"),
      display: "number",
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
