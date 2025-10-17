import { TFunction } from "i18next";

import CustomTask from "@/types/customTask";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) => {
  return createColumnDefs<CustomTask>(({ createData }) => [
    createData("name", {
      label: t("name"),
    }),
    createData("description", {
      label: t("description"),
    }),
  ]);
};

export default getColumns;
