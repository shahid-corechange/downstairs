import { TFunction } from "i18next";

import TimeAdjustment from "@/types/timeAdjustment";

import { createColumnDefs } from "@/utils/dataTable";
import { capitalizeEachWord } from "@/utils/string";

const getColumns = (t: TFunction) =>
  createColumnDefs<TimeAdjustment>(({ createData }) => [
    createData("schedule.startAt", {
      label: t("attendance start"),
      display: "datetime",
      filterable: false,
    }),
    createData("schedule.endAt", {
      label: t("attendance end"),
      display: "datetime",
      filterable: false,
    }),
    createData("schedule.schedule.property.address.fullAddress", {
      label: t("address"),
    }),
    createData("quarters", {
      label: t("quarters"),
      display: "number",
      filterable: false,
    }),
    createData("reason", {
      label: t("reason"),
      filterable: false,
    }),
    createData("causer.fullname", {
      label: t("created by"),
      render: (value) => capitalizeEachWord(value),
    }),
    createData("createdAt", {
      label: t("created at"),
      display: "datetime",
      filterKind: "date",
    }),
  ]);

export default getColumns;
