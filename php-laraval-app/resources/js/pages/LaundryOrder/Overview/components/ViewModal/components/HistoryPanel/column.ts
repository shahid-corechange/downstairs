import { TFunction } from "i18next";

import { LaundryOrderHistory } from "@/types/laundryOrderHistory";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<LaundryOrderHistory>(({ createData }) => [
    createData("type", {
      label: t("type"),
    }),
    createData("note", {
      label: t("note"),
    }),
    createData("causer.name", {
      label: t("causer"),
    }),
    createData("createdAt", {
      label: t("date"),
      display: "datetime",
    }),
  ]);

export default getColumns;
