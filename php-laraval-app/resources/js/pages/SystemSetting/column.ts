import { TFunction } from "i18next";

import GlobalSetting from "@/types/globalSetting";
import Team from "@/types/team";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (
  t: TFunction,
  teams: Team[],
  refillSequences: Record<number, string>,
) =>
  createColumnDefs<GlobalSetting>(({ createData }) => [
    createData("id", {
      label: t("id"),
      display: "number",
      filterable: false,
    }),
    createData("key", { label: "Key" }),
    createData("value", {
      label: t("value"),
      filterable: false,
      sortable: false,
      render: (originalValue, _, context) => {
        if (context.row.original.key === "DEFAULT_SHOWN_TEAM") {
          const teamIds = originalValue.split(",");
          return teams
            .reduce<string[]>((acc, team) => {
              if (teamIds.includes(`${team.id}`)) {
                acc.push(team.name);
              }
              return acc;
            }, [])
            .join(", ");
        }

        if (context.row.original.key === "SUBSCRIPTION_REFILL_SEQUENCE") {
          return refillSequences[Number(originalValue)];
        }

        return originalValue;
      },
    }),
    createData("description", {
      label: t("description"),
    }),
  ]);

export default getColumns;
