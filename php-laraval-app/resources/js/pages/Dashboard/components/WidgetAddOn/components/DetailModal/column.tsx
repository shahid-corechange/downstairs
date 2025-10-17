import { TFunction } from "i18next";

import AddonStatistic from "@/types/addonStatistic";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<AddonStatistic>(({ createData }) => [
    createData("addon.name", {
      label: t("name"),
      filterKind: "autocomplete",
    }),
    createData("credit", {
      label: t("credits"),
      display: "number",
    }),
    createData("currency", {
      label: t("invoice"),
      display: "number",
    }),
    createData("total", {
      label: t("total"),
      display: "number",
    }),
  ]);

export default getColumns;
