import { TFunction } from "i18next";

import { WEEKDAYS } from "@/constants/datetime";
import { FREQUENCIES } from "@/constants/frequency";

import Subscription from "@/types/subscription";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) => {
  return createColumnDefs<Subscription>(({ createData, createAccessor }) => [
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
      getValue: (subscription) => !!subscription.deletedAt,
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("inactive") : t("active"),
        colorScheme: originalValue ? "red" : "green",
      }),
    }),
    createData("user", {
      id: "user.id",
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
          queryKey: ["web", "companies", "subscriptions", "json"],
          select: (response) => response.data.data.map(({ user }) => user),
        },
      },
      render: (originalValue) => originalValue?.fullname ?? "",
      renderOptionsLabel: (value) => value?.fullname ?? "",
      getOptionsValue: (value) => value?.id ?? "",
    }),
    createData("detail.address", {
      label: t("property"),
    }),
    createData("service", {
      id: "serviceId",
      label: t("service"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          include: ["service"],
          only: ["serviceId", "service.name"],
          groupBy: "serviceId",
          sort: { "service.name": "asc" },
        },
        query: {
          queryKey: ["web", "companies", "subscriptions", "json"],
          select: (response) =>
            response.data.data.map(({ service }) => service),
        },
      },
      render: (originalValue) => originalValue?.name ?? "",
      renderOptionsLabel: (value) => value?.name ?? "",
      getOptionsValue: (value) => value?.id ?? "",
    }),
    // TODO: need to implement nested groupBy for groupBy: "detail.teamId" or new route for SubscriptionCleaningDetail
    createData("detail.teamName", {
      // id: "team.id",
      label: t("team"),
      // filterKind: "autocomplete",
      // fetchOptions: {
      //   request: {
      //     show: "all",
      //     size: -1,
      //     include: ["team"],
      //     only: ["team.id", "team.name"],
      //     groupBy: "teamId",
      //     sort: { "team.name": "asc" },
      //   },
      //   query: {
      //     queryKey: ["web", "companies", "subscriptions", "json"],
      //     select: (response) => response.data.data.map(({ team }) => team),
      //   },
      // },
      // render: (originalValue) => originalValue?.name ?? "",
      // renderOptionsLabel: (value) => value?.name ?? "",
      // getOptionsValue: (value) => value?.id ?? "",
    }),
    createData("frequency", {
      label: t("frequency"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["frequency"],
          groupBy: "frequency",
          sort: { frequency: "asc" },
        },
        query: {
          queryKey: ["web", "companies", "subscriptions", "json"],
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
          show: "all",
          size: -1,
          only: ["weekday"],
          groupBy: "weekday",
          sort: { weekday: "asc" },
        },
        query: {
          queryKey: ["web", "companies", "subscriptions", "json"],
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
    createData("endTime", {
      label: t("end time"),
      display: "time",
      filterKind: "time",
    }),
    createData("detail.quarters", {
      label: t("quarters"),
      display: "number",
    }),
    createData("isFixed", {
      label: t("fixed time"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["isFixed"],
          groupBy: "isFixed",
        },
        query: {
          queryKey: ["web", "companies", "subscriptions", "json"],
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
    createData("isPaused", {
      label: t("paused"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["isPaused"],
          groupBy: "isPaused",
        },
        query: {
          queryKey: ["web", "companies", "subscriptions", "json"],
          select: (response) =>
            response.data.data.map(({ isPaused }) => isPaused),
        },
      },
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("yes") : t("no"),
        colorScheme: originalValue ? "green" : "red",
      }),
      renderOptionsLabel: (value) => (value ? t("yes") : t("no")),
    }),
    createAccessor("fixedPriceId", {
      label: t("fixed price"),
      options: [
        { label: t("no"), value: false },
        { label: t("yes"), value: true },
      ],
      filterKind: "autocomplete",
      filterCriteria: (value) => (value === "true" ? "neq" : "eq"),
      filterValueTransformer: () => "null",
      getValue: (subscription) => !!subscription.fixedPriceId,
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("yes") : t("no"),
        colorScheme: originalValue ? "green" : "red",
      }),
    }),
  ]);
};

export default getColumns;
