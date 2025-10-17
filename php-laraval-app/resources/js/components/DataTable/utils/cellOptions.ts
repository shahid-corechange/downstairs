import { Cell } from "@tanstack/react-table";

export const getCellOptions = <T>(cell: Cell<T, unknown>) => {
  const { cellOptions } = cell.column.columnDef.meta || {};

  if (typeof cellOptions === "function") {
    return cellOptions(cell.getValue());
  }

  return cellOptions;
};
