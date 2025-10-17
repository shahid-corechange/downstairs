import { TFunction } from "i18next";

import Schedule from "@/types/schedule";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Schedule>(({ createData }) => [
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
