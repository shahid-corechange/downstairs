import { Box, Image, ListItem, OrderedList, Text } from "@chakra-ui/react";
import { TFunction } from "i18next";

import Empty from "@/components/Empty";

import Team from "@/types/team";

import { createColumnDefs } from "@/utils/dataTable";

import ColorDisplay from "./components/ColorDisplay";

const getColumns = (t: TFunction) =>
  createColumnDefs<Team>(({ createData }) => [
    createData("id", {
      label: t("id"),
      display: "number",
      filterable: false,
    }),
    createData("isActive", {
      label: t("status"),
      display: "boolean",
      filterKind: "autocomplete",
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("active") : t("inactive"),
        colorScheme: originalValue ? "green" : "red",
      }),
      renderOptionsLabel: (value) => (value ? t("active") : t("inactive")),
    }),
    createData("name", { label: t("name") }),
    createData("avatar", {
      label: t("image"),
      filterable: false,
      sortable: false,
      render: (value) => (
        <Box display="grid">
          {value ? (
            <Image
              justifySelf="center"
              boxSize="50px"
              objectFit="cover"
              src={value}
              alt="image"
            />
          ) : (
            <Empty description={<Text>{t("no image")}</Text>} />
          )}
        </Box>
      ),
    }),
    createData("color", {
      label: t("color"),
      filterable: false,
      render: (value) => ColorDisplay(value),
    }),
    createData("users", {
      label: t("worker"),
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
    createData("description", {
      label: t("description"),
      render: (value) => value || "-",
    }),
  ]);

export default getColumns;
