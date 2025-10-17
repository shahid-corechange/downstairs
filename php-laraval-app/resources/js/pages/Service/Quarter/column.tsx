import { TFunction } from "i18next";

import ServiceQuarter from "@/types/serviceQuarter";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<ServiceQuarter>(({ createData }) => [
    createData("service.name", {
      label: t("service"),
      filterKind: "autocomplete",
    }),
    createData("minSquareMeters", {
      label: t("min square meters"),
      display: "number",
      filterKind: "range",
    }),
    createData("maxSquareMeters", {
      label: t("max square meters"),
      display: "number",
      filterKind: "range",
    }),
    createData("quarters", {
      label: t("quarters"),
      display: "number",
      filterKind: "range",
    }),
    createData("hours", {
      label: t("hours"),
      display: "number",
      filterKind: "range",
    }),
  ]);

export default getColumns;
