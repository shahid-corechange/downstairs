import { TFunction } from "i18next";

import Team from "@/types/team";
import User from "@/types/user";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (team: Team, t: TFunction) =>
  createColumnDefs<User>(({ createData, createAccessor }) => [
    createData("fullname", {
      label: t("name"),
    }),
    createAccessor("type", {
      label: t("type"),
      getValue: (worker) =>
        (team.users ?? []).findIndex((user) => user.id === worker.id) > -1,
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("base") : t("temporary"),
        colorScheme: originalValue ? "green" : "blue",
      }),
    }),
  ]);

export default getColumns;
