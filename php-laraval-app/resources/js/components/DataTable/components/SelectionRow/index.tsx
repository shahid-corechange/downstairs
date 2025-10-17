import {
  Button,
  ButtonProps,
  Checkbox,
  Icon,
  MenuItem,
  Td,
  useColorModeValue,
} from "@chakra-ui/react";
import { Row } from "@tanstack/react-table";
import { IconType } from "react-icons";

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
  size?: "xs" | "sm" | "md" | "lg";
  backgroundParent: HTMLElement | null;
  actions?: Actions<T>;
  withEdit?: boolean;
  withDelete?: boolean;
  withRestore?: boolean;
  isOnLeftEnd?: boolean;
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

const SelectionRow = <T extends TableData>({
  row,
  size,
  backgroundParent,
  isOnLeftEnd = false,
}: ActionRowProps<T>) => {
  const bgColor = useColorModeValue(
    "white",
    backgroundParent
      ? window.getComputedStyle(backgroundParent).backgroundColor
      : "transparent",
  );
  const borderColor = useColorModeValue("brand.100", "brand.700");

  return (
    <Td
      w={10}
      pos="sticky"
      left={0}
      p={size === "xs" ? 2 : 4}
      bg={bgColor}
      borderRight={!isOnLeftEnd ? "1px" : "none"}
      borderColor={borderColor}
      boxShadow={
        !isOnLeftEnd
          ? "0px 0px 15px rgba(0, 0, 0, 0.2), 0px 0px 15px rgba(0, 0, 0, 0.06)"
          : "none"
      }
      clipPath="inset(0 -15px 0 0)"
      isNumeric
    >
      <Checkbox
        checked={row.getIsSelected()}
        onChange={row.getToggleSelectedHandler()}
      />
    </Td>
  );
};

export default SelectionRow;
