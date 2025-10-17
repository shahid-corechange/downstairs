import { Button, ButtonProps } from "@chakra-ui/react";
import { Table } from "@tanstack/react-table";

import { TableData } from "@/utils/dataTable";

interface TableAction<T extends TableData>
  extends Omit<ButtonProps, "children" | "onClick"> {
  label: string;
  onClick: (table: Table<T>) => void;
}

interface TableActionProps<T extends TableData> extends TableAction<T> {
  table: Table<T>;
}

export type TableActions<T extends TableData> = TableAction<T>[];

const TableAction = <T extends TableData>({
  table,
  label,
  onClick,
  ...props
}: TableActionProps<T>) => {
  return (
    <Button
      size="sm"
      fontSize="small"
      {...props}
      onClick={() => onClick(table)}
    >
      {label}
    </Button>
  );
};

export default TableAction;
