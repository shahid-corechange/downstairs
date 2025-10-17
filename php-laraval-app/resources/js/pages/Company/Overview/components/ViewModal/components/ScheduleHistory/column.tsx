import { Badge } from "@chakra-ui/react";
import { TFunction } from "i18next";

import Schedule from "@/types/schedule";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Schedule>(({ createData }) => [
    createData("status", {
      label: t("status"),
      filterKind: "autocomplete",
      renderAs: (originalValue) => ({
        type: "badge",
        label: t(originalValue),
        colors: {
          done: "green",
          cancel: "red",
        },
      }),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("canceledType", {
      label: t("canceled by"),
      filterKind: "autocomplete",
      fetchOptions: {
        request: {
          show: "all",
          size: -1,
          only: ["canceledType"],
          groupBy: "canceledType",
          filter: {
            eq: {
              status: "cancel",
            },
          },
        },
        query: {
          queryKey: ["web", "schedules", "json"],
          select: (response) =>
            response.data.data.map(({ canceledType }) => canceledType),
        },
      },
      render: (originalValue) => {
        if (!originalValue) {
          return "-";
        }

        const colors = {
          admin: "orange",
          customer: "blue",
          employee: "green",
        };

        return (
          <Badge
            variant="solid"
            colorScheme={colors[originalValue]}
            p={1.5}
            rounded="md"
            textTransform="capitalize"
          >
            {t(originalValue)}
          </Badge>
        );
      },

      renderOptionsLabel: (value) => t(value ?? "-"),
    }),
    createData("team.name", {
      label: t("team"),
      filterKind: "autocomplete",
    }),
    createData("startAt", {
      label: t("start at"),
      display: "datetime",
      filterCriteria: "gte",
    }),
    createData("endAt", {
      label: t("end at"),
      display: "datetime",
      filterCriteria: "lte",
    }),
    createData("property.address.fullAddress", {
      label: t("address"),
    }),
  ]);

export default getColumns;
