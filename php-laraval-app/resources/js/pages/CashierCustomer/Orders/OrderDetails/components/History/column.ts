import { TFunction } from "i18next";

import { LaundryOrderHistory } from "@/types/laundryOrderHistory";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<LaundryOrderHistory>(({ createData }) => [
    createData("createdAt", {
      label: t("created at"),
      display: "datetime",
      filterKind: "date",
    }),
    createData("type", {
      label: t("type"),
      render: (value) => t(value),
    }),
    createData("note", {
      label: t("note"),
    }),
    createData("causer.fullname", {
      label: t("causer"),
      filterKind: "autocomplete",
    }),
  ]);

export default getColumns;
