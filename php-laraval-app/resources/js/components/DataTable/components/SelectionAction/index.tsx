import { Button, ButtonProps } from "@chakra-ui/react";
import { Row } from "@tanstack/react-table";

import { TableData } from "@/utils/dataTable";

interface SelectionAction<T extends TableData>
  extends Omit<ButtonProps, "children" | "onClick"> {
  label: string;
  onClick: (rows: Row<T>[]) => void;
}

interface SelectionActionProps<T extends TableData> extends SelectionAction<T> {
  rows: Row<T>[];
}

export type SelectionActions<T extends TableData> = SelectionAction<T>[];

const SelectionAction = <T extends TableData>({
  rows,
  label,
  onClick,
  ...props
}: SelectionActionProps<T>) => {
  return (
    <Button size="sm" fontSize="small" {...props} onClick={() => onClick(rows)}>
      {label}
    </Button>
  );
};

export default SelectionAction;
