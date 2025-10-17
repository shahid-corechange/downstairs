import { TFunction } from "i18next";

import Schedule from "@/types/schedule";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Schedule>(({ createData }) => [
    createData("detail.subscription.user.fullname", {
      label: t("customer"),
      filterKind: "autocomplete",
    }),
    createData("team.name", {
      label: t("team"),
      filterKind: "autocomplete",
    }),
    createData("startAt", {
      label: t("start at"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("endAt", {
      label: t("end at"),
      display: "datetime",
      filterKind: "date",
    }),
  ]);

export default getColumns;
