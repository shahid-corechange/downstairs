import { Flex, Image, ListItem, OrderedList, Text } from "@chakra-ui/react";
import { TFunction } from "i18next";

import Empty from "@/components/Empty";

import Product from "@/types/product";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Product>(({ createData, createAccessor }) => [
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
    createData("thumbnailImage", {
      label: t("image"),
      filterable: false,
      sortable: false,
      render: (value, _, context) =>
        value ? (
          <Flex
            justify="center"
            bg={context.row.original?.color ?? "gray.500"}
            rounded="md"
            p={1}
          >
            <Image objectFit="cover" src={value} alt="thumbnail" w="50px" />
          </Flex>
        ) : (
          <Empty
            imageProps={{ w: "50px" }}
            description={
              <Text align="center" fontSize="xs" color="gray.500">
                {t("no image")}
              </Text>
            }
          />
        ),
    }),
    createData("name", { label: t("name") }),
    createData("unit", {
      label: t("unit"),
      filterKind: "autocomplete",
    }),
    createData("categories", {
      label: t("categories"),
      display: "list",
      filterKind: "autocomplete",
      render: (originalValue) => (
        <OrderedList>
          {originalValue?.map((category) => (
            <ListItem key={category.id}>{category.name}</ListItem>
          ))}
        </OrderedList>
      ),
      renderOptionsLabel: (value) => value?.name ?? "",
      getOptionsValue: (value) => value?.id ?? "",
      sortable: false,
    }),
    createData("priceWithVat", {
      label: t("price"),
      display: "currency",
    }),
    createData("creditPrice", {
      label: t("credit price"),
      display: "number",
    }),
    createData("vatGroup", { label: t("vat"), display: "number" }),
    createData("stores", {
      label: t("stores"),
      display: "list",
      filterKind: "autocomplete",
      render: (originalValue) => (
        <OrderedList>
          {originalValue?.map((store) => (
            <ListItem key={store.id}>{store.name}</ListItem>
          ))}
        </OrderedList>
      ),
      renderOptionsLabel: (value) => value?.name ?? "",
      getOptionsValue: (value) => value?.id ?? "",
      sortable: false,
    }),
    createData("hasRut", {
      label: t("rut"),
      display: "boolean",
      filterKind: "autocomplete",
      renderAs: (originalValue) => ({
        type: "badge",
        label: originalValue ? t("yes") : t("no"),
        colorScheme: originalValue ? "green" : "red",
      }),
      renderOptionsLabel: (value) => (value ? t("yes") : t("no")),
    }),
  ]);

export default getColumns;
