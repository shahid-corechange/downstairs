import { Box, Image, Text } from "@chakra-ui/react";
import { TFunction } from "i18next";

import Empty from "@/components/Empty";

import Service from "@/types/service";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Service>(({ createData, createAccessor }) => [
    createData("id", {
      label: t("id"),
      display: "number",
      filterable: false,
    }),
    createAccessor("deletedAt", {
      label: t("status"),
      display: "boolean",
      filterKind: "autocomplete",
      getValue: (service) => !!service.deletedAt,
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
    createData("name", { label: t("name") }),
    createData("membershipType", {
      label: t("membership type"),
      filterKind: "autocomplete",
      render: (originalValue) => t(originalValue),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("type", {
      label: t("type"),
      filterKind: "autocomplete",
      render: (originalValue) => t(originalValue),
      renderOptionsLabel: (value) => t(value),
    }),
    createData("priceWithVat", {
      label: t("price per quarter"),
      display: "currency",
    }),
    createData("vatGroup", {
      label: t("vat"),
      filterKind: "autocomplete",
      display: "number",
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
