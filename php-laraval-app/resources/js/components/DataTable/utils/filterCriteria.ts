import { Column } from "@tanstack/react-table";

export const getDefaultFilterCriteria = <T>(column: Column<T>) => {
  const { display, filterKind, autocompleteFreeMode } =
    column.columnDef.meta || {};

  if (
    display === "boolean" ||
    filterKind === "select" ||
    (display !== "list" &&
      filterKind === "autocomplete" &&
      !autocompleteFreeMode)
  ) {
    return "eq";
  } else if (display === "list") {
    return "in";
  }

  return "like";
};
