import {
  Button,
  ButtonProps,
  Icon,
  IconButton,
  Menu,
  MenuButton,
  MenuItem,
  MenuList,
  Portal,
  Td,
  useColorModeValue,
} from "@chakra-ui/react";
import { Row } from "@tanstack/react-table";
import { useMemo } from "react";
import { useTranslation } from "react-i18next";
import { IconType } from "react-icons";
import { FiEdit3 } from "react-icons/fi";
import { LuTrash } from "react-icons/lu";
import { PiDotsThreeOutlineVertical } from "react-icons/pi";
import { TbRestore } from "react-icons/tb";

import { ActionModalType } from "@/components/DataTable/types";

import { TableData } from "@/utils/dataTable";

interface Action<T extends TableData>
  extends Omit<ButtonProps, "icon" | "children" | "isDisabled" | "onClick"> {
  label: string;
  icon: IconType;
  onClick: (row: Row<T>) => void;
  isDisabled?: boolean | ((row: Row<T>) => boolean);
  isHidden?: boolean | ((row: Row<T>) => boolean);
}

export type Actions<T extends TableData> = (
  | Action<T>
  | ((row: Row<T>) => Action<T> | null)
)[];

interface ActionProps<T extends TableData> extends Action<T> {
  row: Row<T>;
}

interface ActionRowProps<T extends TableData> {
  row: Row<T>;
  onAction: (modal: ActionModalType) => void;
  backgroundParent: HTMLElement | null;
  actions?: Actions<T>;
  withEdit?: boolean;
  withDelete?: boolean;
  withRestore?: boolean;
  isOnRightEnd?: boolean;
  size?: "xs" | "sm" | "md" | "lg";
}

const Action = <T extends TableData>({
  row,
  label,
  icon,
  onClick,
  isDisabled,
  isHidden,
  ...props
}: ActionProps<T>) => {
  return (typeof isHidden === "function" ? !isHidden(row) : !isHidden) ? (
    <MenuItem
      as={Button}
      key={label}
      variant="ghost"
      colorScheme="gray"
      minH={10}
      fontSize="small"
      fontWeight="normal"
      rounded="none"
      icon={<Icon as={icon} boxSize={4} />}
      onClick={() => onClick(row)}
      isDisabled={
        typeof isDisabled === "function" ? isDisabled(row) : isDisabled
      }
      {...props}
    >
      {label}
    </MenuItem>
  ) : null;
};

const ActionRow = <T extends TableData>({
  row,
  onAction,
  backgroundParent,
  withEdit,
  withDelete,
  withRestore,
  actions = [],
  isOnRightEnd = false,
  size = "sm",
}: ActionRowProps<T>) => {
  const { t } = useTranslation();
  const bgColor = useColorModeValue(
    "white",
    backgroundParent
      ? window.getComputedStyle(backgroundParent).backgroundColor
      : "transparent",
  );
  const borderColor = useColorModeValue("brand.100", "brand.700");

  const filteredActions = useMemo(
    () =>
      actions.reduce((acc, value) => {
        if (typeof value === "function") {
          const action = value(row);
          if (
            action &&
            (typeof action.isHidden === "function"
              ? !action.isHidden(row)
              : !action.isHidden || action.isHidden === undefined)
          ) {
            acc.push(action);
          }
        } else {
          if (
            typeof value.isHidden === "function"
              ? !value.isHidden(row)
              : !value.isHidden || value.isHidden === undefined
          ) {
            acc.push(value);
          }
        }
        return acc;
      }, [] as Action<T>[]),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [actions],
  );

  return (filteredActions && filteredActions.length > 0) ||
    withEdit ||
    withDelete ||
    withRestore ? (
    <Td
      w={10}
      pos="sticky"
      right={0}
      p={size === "xs" ? 2 : 4}
      bg={bgColor}
      borderLeft={!isOnRightEnd ? "1px" : "none"}
      borderColor={borderColor}
      boxShadow={
        !isOnRightEnd
          ? "0px 0px 15px rgba(0, 0, 0, 0.2), 0px 0px 15px rgba(0, 0, 0, 0.06)"
          : "none"
      }
      clipPath="inset(0 0 0 -15px)"
      isNumeric
    >
      <Menu>
        <MenuButton
          as={IconButton}
          variant="ghost"
          alignItems="center"
          lineHeight={0}
        >
          <Icon as={PiDotsThreeOutlineVertical} boxSize={4} />
        </MenuButton>
        <Portal>
          <MenuList
            minW={32}
            display="flex"
            flexDirection="column"
            zIndex="popover"
          >
            {filteredActions.map((action, i) => (
              <Action key={i} row={row} {...action} />
            ))}
            {withEdit && (
              <MenuItem
                as={Button}
                variant="ghost"
                colorScheme="gray"
                minH={10}
                fontSize="small"
                fontWeight="normal"
                rounded="none"
                icon={<Icon as={FiEdit3} boxSize={4} />}
                onClick={() => onAction("edit")}
              >
                {t("edit")}
              </MenuItem>
            )}
            {withDelete && (
              <MenuItem
                as={Button}
                variant="ghost"
                colorScheme="red"
                minH={10}
                fontSize="small"
                fontWeight="normal"
                rounded="none"
                color="red.500"
                _dark={{ color: "red.200" }}
                icon={<Icon as={LuTrash} boxSize={4} />}
                onClick={() => onAction("delete")}
              >
                {t("delete")}
              </MenuItem>
            )}
            {withRestore && !withDelete && (
              <MenuItem
                as={Button}
                variant="ghost"
                colorScheme="gray"
                minH={10}
                fontSize="small"
                fontWeight="normal"
                rounded="none"
                icon={<Icon as={TbRestore} boxSize={4} />}
                onClick={() => onAction("restore")}
              >
                {t("restore")}
              </MenuItem>
            )}
          </MenuList>
        </Portal>
      </Menu>
    </Td>
  ) : (
    <Td
      w={10}
      pos="sticky"
      right={0}
      p={size === "xs" ? 2 : 4}
      bg={bgColor}
      borderLeft={!isOnRightEnd ? "1px" : "none"}
      borderColor={borderColor}
      boxShadow={!isOnRightEnd ? "xl" : "none"}
      isNumeric
    />
  );
};

export default ActionRow;
