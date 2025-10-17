import { TFunction } from "i18next";

import Feedback from "@/types/feedback";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Feedback>(({ createData, createAccessor }) => [
    createAccessor("deletedAt", {
      label: t("status"),
      options: [
        { label: t("active"), value: false },
        { label: t("inactive"), value: true },
      ],
      filterKind: "autocomplete",
      filterCriteria: (value) => (value === "true" ? "neq" : "eq"),
      filterValueTransformer: () => "null",
      getValue: (feedback) => !!feedback.deletedAt,
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("deleted") : t("active"),
        colorScheme: originalValue ? "red" : "green",
      }),
    }),
    createData("user", {
      id: "user.id",
      label: t("user"),
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
          queryKey: ["web", "feedbacks", "json"],
          select: (response) => response.data.data.map(({ user }) => user),
        },
      },
      render: (originalValue) => originalValue?.fullname ?? "",
      renderOptionsLabel: (value) => value?.fullname ?? "",
      getOptionsValue: (value) => value?.id ?? "",
    }),
    createData("option", {
      label: t("option"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["option"],
          groupBy: "option",
          sort: { option: "asc" },
        },
        query: {
          queryKey: ["web", "feedbacks", "json"],
          select: (response) => response.data.data.map(({ option }) => option),
        },
      },
      render: (originalValue) => t(originalValue),
    }),
    createData("description", {
      label: t("description"),
    }),
    createData("createdAt", {
      label: t("created at"),
      display: "datetime",
      filterKind: "date",
    }),
  ]);

export default getColumns;
