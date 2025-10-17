import { ListItem, Text, UnorderedList } from "@chakra-ui/react";
import { TFunction } from "i18next";

import PERMISSIONS from "@/constants/permission";

import { Role } from "@/types/authorization";

import { createColumnDefs } from "@/utils/dataTable";

const getColumns = (t: TFunction) =>
  createColumnDefs<Role>(({ createData }) => [
    createData("id", {
      label: t("id"),
      display: "number",
      filterable: false,
    }),
    createData("name", { label: t("name") }),
    createData("permissions", {
      label: t("permissions"),
      display: "list",
      filterKind: "autocomplete",
      render: (permissions, _, context) => {
        if (context.row.original.name === "Superadmin") {
          return t("all");
        } else if (!permissions || permissions.length === 0) {
          return t("none");
        }

        const groupedPermissions = permissions.reduce<Record<string, string[]>>(
          (acc, { name }) => {
            const permission = PERMISSIONS[name as keyof typeof PERMISSIONS];
            const { group } = permission;
            acc[group] = [...(acc[group] || []), permission.label];
            return acc;
          },
          {},
        );

        return (
          <UnorderedList>
            {Object.entries(groupedPermissions).map(([group, values]) => (
              <ListItem key={group}>
                <Text>{t(group)}</Text>
                <Text color="GrayText">{` (${values
                  .map((value) => t(value))
                  .join(", ")})`}</Text>
              </ListItem>
            ))}
          </UnorderedList>
        );
      },
      renderOptionsLabel: (permission) => {
        if (!permission) {
          return t("none");
        }

        const fullPermission =
          PERMISSIONS[permission.name as keyof typeof PERMISSIONS];
        return `${t(fullPermission.group)} (${t(fullPermission.label)})`;
      },
      getOptionsValue: (permission) => permission?.id ?? false,
    }),
  ]);

export default getColumns;
