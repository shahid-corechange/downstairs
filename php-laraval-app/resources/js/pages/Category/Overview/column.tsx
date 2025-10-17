import { Box, Image, Text } from "@chakra-ui/react";
import { TFunction } from "i18next";

import Empty from "@/components/Empty";

import Category from "@/types/category";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Category>(({ createData, createAccessor }) => [
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
      render: (value) => (
        <Box>
          {value ? (
            <Image
              h="100px"
              w="full"
              objectFit="contain"
              src={value}
              alt="image"
            />
          ) : (
            <Empty description={<Text>{t("no image")}</Text>} />
          )}
        </Box>
      ),
    }),
    createData("name", {
      label: t("name"),
      filterKind: "autocomplete",
    }),
    createData("description", {
      label: t("description"),
      filterKind: "autocomplete",
    }),
  ]);

export default getColumns;
