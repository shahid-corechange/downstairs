import { TFunction } from "i18next";

import { RutCoApplicant } from "@/types/rutCoApplicant";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<RutCoApplicant>(({ createData }) => [
    createData("isEnabled", {
      label: t("status"),
      display: "boolean",
      filterKind: "autocomplete",
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("enabled") : t("disabled"),
        colorScheme: originalValue ? "green" : "red",
      }),
      renderOptionsLabel: (value) => (value ? t("enabled") : t("disabled")),
    }),
    createData("isPaused", {
      label: t("paused"),
      display: "boolean",
      filterKind: "autocomplete",
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("yes") : t("no"),
        colorScheme: originalValue ? "red" : "green",
      }),
      renderOptionsLabel: (value) => (value ? t("yes") : t("no")),
    }),
    createData("name", {
      label: t("name"),
    }),
    createData("identityNumber", {
      label: t("identity number"),
    }),
    createData("formattedPhone", {
      label: t("phone"),
    }),
    createData("pauseStartDate", {
      label: t("pause start"),
      display: "date",
      filterKind: "date",
      dateFormat: "MMMM",
    }),
    createData("pauseEndDate", {
      label: t("pause end"),
      display: "date",
      filterKind: "date",
      dateFormat: "MMMM",
    }),
  ]);

export default getColumns;
