import { ListItem, UnorderedList } from "@chakra-ui/react";
import { TFunction } from "i18next";

import KeyPlace from "@/types/keyplace";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<KeyPlace>(({ createData, createAccessor }) => [
    createData("id", {
      label: t("key place"),
      display: "number",
    }),
    createAccessor("property.address.address", {
      label: t("address"),
      getValue: (value) => value.property?.address?.fullAddress ?? "",
    }),
    createAccessor("property.membershipType", {
      label: t("type"),
      filterKind: "autocomplete",
      getValue: (value) => value.property?.membershipType ?? "empty",
      fetchOptions: {
        request: {
          filter: {
            neq: {
              propertyId: "null",
            },
          },
          include: ["property"],
          only: ["property.membershipType"],
          size: -1,
          show: "all",
          sort: {
            "property.membershipType": "asc",
          },
        },
        query: {
          queryKey: ["web", "keyplaces", "json"],
          select: (response) =>
            response.data.data.map(
              ({ property }) => property?.membershipType ?? "",
            ),
        },
      },
      renderAs: (originalValue) => ({
        type: "badge",
        label: t(originalValue),
        colors: {
          company: "blue",
          private: "orange",
          empty: "gray",
        },
      }),
      renderOptionsLabel: (value) => t(value),
    }),
    createAccessor("property.users", {
      id: "property.users.id",
      label: t("customers"),
      display: "list",
      filterKind: "autocomplete",
      getValue: (value) => value.property?.users ?? [],
      fetchOptions: {
        request: {
          filter: {
            neq: {
              propertyId: "null",
            },
          },
          include: ["property.users"],
          only: ["property.users.id", "property.users.fullname"],
          size: -1,
          show: "all",
          sort: {
            "property.users.id": "asc",
          },
        },
        query: {
          queryKey: ["web", "keyplaces", "json"],
          select: (response) =>
            response.data.data.map(({ property }) => property?.users ?? []),
        },
      },
      render: (originalValue) =>
        originalValue.length > 0 ? (
          <UnorderedList>
            {originalValue.map((user) => (
              <ListItem key={user.id}>{user.fullname}</ListItem>
            ))}
          </UnorderedList>
        ) : (
          "-"
        ),
      renderOptionsLabel: (value) => value.fullname,
      getOptionsValue: (value) => value.id,
    }),
  ]);

export default getColumns;
