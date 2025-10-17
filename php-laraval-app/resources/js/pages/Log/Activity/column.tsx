import { ListItem, UnorderedList } from "@chakra-ui/react";
import { TFunction } from "i18next";

import ActivityLog from "@/types/activityLog";

import { createColumnDefs } from "@/utils/dataTable";
import { pascalToSentence } from "@/utils/string";

const getColumns = (t: TFunction) =>
  createColumnDefs<ActivityLog>(({ createData }) => [
    createData("subjectId", {
      label: t("subject id"),
      display: "number",
    }),
    createData("subjectType", {
      label: t("subject type"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          size: -1,
          only: ["subjectType"],
          groupBy: "subjectType",
          sort: { subjectType: "asc" },
        },
        query: {
          queryKey: ["web", "log", "activities", "json"],
          select: (response) =>
            response.data.data.map(({ subjectType }) => subjectType),
        },
      },
      render: (originalValue) =>
        pascalToSentence(originalValue.replace("App\\Models\\", "")),
      renderOptionsLabel: (value) =>
        pascalToSentence(value.replace("App\\Models\\", "")),
    }),
    createData("event", {
      label: t("event"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          size: -1,
          only: ["event"],
          groupBy: "event",
          sort: { event: "asc" },
        },
        query: {
          queryKey: ["web", "log", "activities", "json"],
          select: (response) => response.data.data.map(({ event }) => event),
        },
      },
      renderAs: (originalValue) => ({
        type: "badge",
        label: t(originalValue),
        colors: {
          created: "green",
          updated: "orange",
          deleted: "red",
          restored: "blue",
        },
      }),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("user", {
      id: "causerId",
      label: t("causer"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          size: -1,
          include: ["user"],
          only: ["user.id", "user.fullname"],
          groupBy: "causerId",
          sort: { "user.fullname": "asc" },
        },
        query: {
          queryKey: ["web", "log", "activities", "json"],
          select: (response) => response.data.data.map(({ user }) => user),
        },
      },
      render: (originalValue) => originalValue?.fullname ?? "",
      renderOptionsLabel: (value) => value?.fullname ?? "",
      getOptionsValue: (value) => value?.id ?? "",
    }),
    createData("user.roles", {
      id: "causer.roles.id",
      label: t("roles"),
      display: "list",
      filterKind: "autocomplete",
      sortable: false,
      render: (originalValue) => (
        <UnorderedList>
          {originalValue?.map((role) => (
            <ListItem key={role.id}>{role.name}</ListItem>
          ))}
        </UnorderedList>
      ),
      renderOptionsLabel: (value) => value?.name ?? "",
      getOptionsValue: (value) => value?.id ?? "",
    }),
    createData("createdAt", {
      label: t("created at"),
      display: "datetime",
      filterKind: "date",
    }),
  ]);

export default getColumns;
