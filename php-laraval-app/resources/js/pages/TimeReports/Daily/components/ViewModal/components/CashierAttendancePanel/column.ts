import { TFunction } from "i18next";

import CashierAttendance from "@/types/cashierAttendance";

import { createColumnDefs } from "@/utils/dataTable";
import { interpretTotalHours } from "@/utils/time";

const getColumns = (t: TFunction) =>
  createColumnDefs<CashierAttendance>(({ createData }) => [
    createData("store.name", {
      label: t("store"),
      filterable: false,
    }),
    createData("checkInAt", {
      label: t("check in at"),
      display: "datetime",
      filterable: false,
    }),
    createData("checkInCauser.fullname", {
      label: t("check in by"),
      filterable: false,
    }),
    createData("checkOutAt", {
      label: t("check out at"),
      display: "datetime",
      filterable: false,
    }),
    createData("checkOutCauser.fullname", {
      label: t("check out by"),
      filterable: false,
    }),
    createData("totalHours", {
      label: t("total time"),
      display: "number",
      filterable: false,
      render: (value) => interpretTotalHours(value),
    }),
  ]);

export default getColumns;
