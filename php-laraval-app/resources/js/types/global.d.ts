import { TableCellProps } from "@chakra-ui/react";
import { AxiosInstance } from "axios";

import { AutocompleteOption } from "@/components/Autocomplete/types";

import { TableData } from "@/utils/dataTable";
import { RequestQueryStringFilter } from "@/utils/request";

import { Primitive } from ".";
import { Response } from "./api";
import { QueryOptions } from "./request";

declare global {
  interface Window {
    axios: AxiosInstance;
  }
}

declare module "@tanstack/table-core" {
  interface TableMeta {
    setColumnFilterClause: (id: string, clause: "and" | "or") => void;
  }

  interface ColumnMeta<TData extends TableData, TValue = unknown> {
    autocompleteFreeMode?: boolean;
    cellOptions?:
      | Omit<TableCellProps, "children" | "fontSize" | "textAlign">
      | ((
          value: TValue extends unknown[] ? TValue[number] : TValue,
        ) => Omit<TableCellProps, "children" | "fontSize" | "textAlign">);
    dateFormat?: string;
    display?:
      | "boolean"
      | "currency"
      | "date"
      | "localDate"
      | "datetime"
      | "number"
      | "phone"
      | "list"
      | "time";
    filterKind?:
      | "input"
      | "select"
      | "autocomplete"
      | "date"
      | "time"
      | "range";
    filterCriteria?:
      | keyof RequestQueryStringFilter<TData>
      | ((filterValue: Primitive) => keyof RequestQueryStringFilter<TData>);
    filterValueTransformer?: (value: Primitive) => Primitive;
    fetchOptions?: Required<
      QueryOptions<TData, Response<TData[]>, TValue[], unknown, string[]>
    >;
    options?: string[] | AutocompleteOption[];
    renderOptions?: (
      values: TValue extends unknown[] ? TValue : TValue[],
    ) => AutocompleteOption[];
    renderOptionsLabel?: (
      value: TValue extends unknown[] ? TValue[number] : TValue,
    ) => string;
    getOptionsValue?: (
      value: TValue extends unknown[] ? TValue[number] : TValue,
    ) => Primitive;
  }
}
