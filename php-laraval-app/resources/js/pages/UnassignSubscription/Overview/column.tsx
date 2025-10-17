import { TFunction } from "i18next";

import { WEEKDAYS } from "@/constants/datetime";
import { FREQUENCIES } from "@/constants/frequency";

import UnassignSubscription from "@/types/unassignSubscription";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) => {
  return createColumnDefs<UnassignSubscription>(
    ({ createData, createAccessor }) => [
      createData("id", {
        label: t("id"),
        display: "number",
        filterable: false,
      }),
      createData("user", {
        id: "user.id",
        label: t("customer"),
        filterKind: "autocomplete",
        fetchOptions: {
          request: {
            size: -1,
            include: ["user"],
            only: ["user.id", "user.fullname"],
            groupBy: "userId",
            sort: { "user.fullname": "asc" },
          },
          query: {
            queryKey: ["web", "unassign-subscriptions", "json"],
            select: (response) => response.data.data.map(({ user }) => user),
          },
        },
        render: (originalValue) => originalValue?.fullname ?? "",
        renderOptionsLabel: (value) => value?.fullname ?? "",
        getOptionsValue: (value) => value?.id ?? "",
      }),
      createData("propertyAddress", {
        label: t("property"),
      }),
      createData("service", {
        id: "serviceId",
        label: t("service"),
        filterKind: "autocomplete",
        fetchOptions: {
          request: {
            size: -1,
            include: ["service"],
            only: ["serviceId", "service.name"],
            groupBy: "serviceId",
            sort: { "service.name": "asc" },
          },
          query: {
            queryKey: ["web", "unassign-subscriptions", "json"],
            select: (response) =>
              response.data.data.map(({ service }) => service),
          },
        },
        render: (originalValue) => originalValue?.name ?? "",
        renderOptionsLabel: (value) => value?.name ?? "",
        getOptionsValue: (value) => value?.id ?? "",
      }),
      createData("frequency", {
        label: t("frequency"),
        filterKind: "autocomplete",
        fetchOptions: {
          request: {
            size: -1,
            only: ["frequency"],
            groupBy: "frequency",
            sort: { frequency: "asc" },
          },
          query: {
            queryKey: ["web", "unassign-subscriptions", "json"],
            select: (response) =>
              response.data.data.map(({ frequency }) => frequency),
          },
        },
        render: (originalValue) =>
          t(FREQUENCIES[originalValue as keyof typeof FREQUENCIES]),
        renderOptionsLabel: (value) =>
          t(FREQUENCIES[value as keyof typeof FREQUENCIES]),
      }),
      createData("weekday", {
        label: t("weekday"),
        filterKind: "autocomplete",
        fetchOptions: {
          request: {
            size: -1,
            only: ["weekday"],
            groupBy: "weekday",
            sort: { weekday: "asc" },
          },
          query: {
            queryKey: ["web", "unassign-subscriptions", "json"],
            select: (response) =>
              response.data.data.map(({ weekday }) => weekday),
          },
        },
        render: (originalValue) => t(WEEKDAYS[originalValue - 1]),
        renderOptionsLabel: (value) => t(WEEKDAYS[value - 1]),
      }),
      createData("startAt", {
        label: t("start date"),
        display: "localDate",
        filterKind: "date",
      }),
      createData("endAt", {
        label: t("end date"),
        display: "date",
        filterKind: "date",
      }),
      createData("startTime", {
        label: t("start time"),
        display: "time",
        filterKind: "time",
      }),
      createData("quarters", {
        label: t("quarters"),
        display: "number",
      }),
      createData("isFixed", {
        label: t("fixed time"),
        filterKind: "autocomplete",
        fetchOptions: {
          request: {
            size: -1,
            only: ["isFixed"],
            groupBy: "isFixed",
          },
          query: {
            queryKey: ["web", "unassign-subscriptions", "json"],
            select: (response) =>
              response.data.data.map(({ isFixed }) => isFixed),
          },
        },
        renderAs: (originalValue) => ({
          type: "badge",
          label: originalValue ? t("yes") : t("no"),
          colorScheme: originalValue ? "green" : "red",
        }),
        renderOptionsLabel: (value) => (value ? t("yes") : t("no")),
      }),
      createAccessor("fixedPrice", {
        label: t("fixed price"),
        options: [
          { label: t("no"), value: false },
          { label: t("yes"), value: true },
        ],
        filterKind: "autocomplete",
        filterCriteria: (value) => (value === "true" ? "neq" : "eq"),
        filterValueTransformer: () => "null",
        getValue: (subscription) => !!subscription.fixedPrice,
        renderAs: (originalValue) => ({
          type: "badge",
          label: originalValue ? t("yes") : t("no"),
          colorScheme: originalValue ? "green" : "red",
        }),
      }),
    ],
  );
};

export default getColumns;
