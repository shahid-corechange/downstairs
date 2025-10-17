import { ListItem, UnorderedList } from "@chakra-ui/react";
import { TFunction } from "i18next";

import ScheduleDeviation from "@/types/scheduleDeviation";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<ScheduleDeviation>(({ createData }) => [
    createData("schedule.user.fullname", {
      label: t("customer"),
    }),
    createData("schedule.team.name", {
      label: t("team"),
    }),
    createData("schedule.startAt", {
      label: t("schedule start"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("schedule.endAt", {
      label: t("schedule end"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("types", {
      label: t("types"),
      display: "list",
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["types"],
          groupBy: "types",
          sort: { types: "asc" },
        },
        query: {
          queryKey: ["web", "deviations", "json"],
          select: (response) => response.data.data.map(({ types }) => types),
        },
      },
      render: (originalValue) => (
        <UnorderedList>
          {originalValue.map((type) => (
            <ListItem key={type}>{t(`deviation type ${type}`)}</ListItem>
          ))}
        </UnorderedList>
      ),
      renderOptionsLabel: (value) => t(`deviation type ${value}`),
    }),
    createData("isHandled", {
      label: t("is handled"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["isHandled"],
          groupBy: "isHandled",
        },
        query: {
          queryKey: ["web", "deviations", "json"],
          select: (response) =>
            response.data.data.map(({ isHandled }) => isHandled),
        },
      },
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("yes") : t("no"),
        colorScheme: originalValue ? "green" : "red",
      }),
      renderOptionsLabel: (value) => (value ? t("yes") : t("no")),
    }),
  ]);

export default getColumns;
