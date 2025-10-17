import { Badge } from "@chakra-ui/react";
import { TFunction } from "i18next";

import Schedule from "@/types/schedule";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Schedule>(({ createData }) => [
    createData("canceledBy", {
      label: t("canceled by"),
      filterKind: "autocomplete",
      renderOptionsLabel: (value) => t(value ?? "-"),
    }),
    createData("canceledType", {
      label: t("canceled type"),
      filterKind: "autocomplete",
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
    createData("customer.name", {
      label: t("customer"),
      filterKind: "autocomplete",
    }),
    createData("team.name", {
      label: t("team"),
      filterKind: "autocomplete",
    }),
    createData("canceledAt", {
      label: t("canceled at"),
      display: "datetime",
      filterable: false,
    }),
    createData("startAt", {
      label: t("schedule start"),
      display: "datetime",
      filterable: false,
    }),
    createData("endAt", {
      label: t("schedule end"),
      display: "datetime",
      filterable: false,
    }),
  ]);

export default getColumns;
