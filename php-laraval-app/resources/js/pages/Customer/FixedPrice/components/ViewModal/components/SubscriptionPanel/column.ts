import { TFunction } from "i18next";

import { WEEKDAYS } from "@/constants/datetime";
import { FREQUENCIES } from "@/constants/frequency";

import Subscription from "@/types/subscription";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Subscription>(({ createData }) => [
    createData("id", {
      label: t("id"),
      display: "number",
      filterable: false,
    }),
    createData("detail.address", {
      label: t("property"),
    }),
    createData("service.name", {
      label: t("service"),
      filterKind: "autocomplete",
    }),
    createData("detail.teamName", {
      label: t("team"),
      filterKind: "autocomplete",
    }),
    createData("frequency", {
      label: t("frequency"),
      filterKind: "autocomplete",
      render: (originalValue) =>
        t(FREQUENCIES[originalValue as keyof typeof FREQUENCIES]),
      renderOptionsLabel: (value) =>
        t(FREQUENCIES[value as keyof typeof FREQUENCIES]),
    }),
    createData("weekday", {
      label: t("weekday"),
      filterKind: "autocomplete",
      render: (originalValue) => t(WEEKDAYS[originalValue - 1]),
      renderOptionsLabel: (value) => t(WEEKDAYS[value - 1]),
    }),
    createData("startTime", {
      label: t("start time"),
      display: "time",
      filterKind: "time",
    }),
    createData("endTime", {
      label: t("end time"),
      display: "time",
      filterKind: "time",
    }),
  ]);

export default getColumns;
