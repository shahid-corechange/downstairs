import { TFunction } from "i18next";

import ScheduleEmployee from "@/types/scheduleEmployee";

import { createColumnDefs } from "@/utils/dataTable";

interface StatusConfig {
  label: string;
  colorScheme: string;
}

const getColumns = (t: TFunction) =>
  createColumnDefs<ScheduleEmployee>(({ createData, createAccessor }) => [
    createData("user.fullname", {
      label: t("name"),
    }),
    createAccessor("type", {
      label: t("type"),
      getValue: (scheduleEmployee) =>
        (scheduleEmployee.schedule?.team?.users ?? []).findIndex(
          (user) => user.id === scheduleEmployee.userId,
        ) > -1,
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("base") : t("temporary"),
        colorScheme: originalValue ? "green" : "blue",
      }),
    }),
    createAccessor("status", {
      label: t("status"),
      getValue: (scheduleEmployee) => {
        if (scheduleEmployee.status === "cancel") {
          return "cancel";
        }
        return !!scheduleEmployee.deletedAt;
      },
      renderAs: (originalValue) => {
        const statusConfig: Record<string, StatusConfig> = {
          cancel: { label: t("canceled"), colorScheme: "red" },
          true: { label: t("deleted"), colorScheme: "red" },
          false: { label: t("enabled"), colorScheme: "green" },
        };

        return {
          type: "badge",
          ...statusConfig[String(originalValue)],
        };
      },
    }),
  ]);

export default getColumns;
