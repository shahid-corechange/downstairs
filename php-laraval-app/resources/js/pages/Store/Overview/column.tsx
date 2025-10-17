import { ListItem, OrderedList } from "@chakra-ui/react";
import { TFunction } from "i18next";

import { Store } from "@/types/store";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Store>(({ createData, createAccessor }) => [
    createData("id", {
      label: t("id"),
      display: "number",
      filterable: false,
    }),
    createAccessor("deletedAt", {
      label: t("status"),
      display: "boolean",
      filterKind: "autocomplete",
      getValue: (product) => !!product.deletedAt,
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("inactive") : t("active"),
        colorScheme: originalValue ? "red" : "green",
      }),
      renderOptionsLabel: (value) => (value ? t("inactive") : t("active")),
    }),
    createData("name", {
      label: t("name"),
      filterKind: "autocomplete",
    }),
    createData("companyNumber", {
      label: t("organization number"),
      display: "number",
    }),
    createData("email", {
      label: t("email"),
    }),
    createData("formattedPhone", {
      label: t("phone"),
      display: "phone",
    }),
    createData("address.fullAddress", {
      label: t("address"),
      filterKind: "autocomplete",
    }),
    createData("users", {
      label: t("employees"),
      display: "list",
      filterKind: "autocomplete",
      render: (originalValue) => (
        <OrderedList>
          {originalValue?.map((user) => (
            <ListItem key={user.id}>{user.fullname}</ListItem>
          ))}
        </OrderedList>
      ),
      renderOptionsLabel: (value) => value?.fullname ?? "",
      getOptionsValue: (value) => value?.id ?? "",
      sortable: false,
    }),
  ]);

export default getColumns;
