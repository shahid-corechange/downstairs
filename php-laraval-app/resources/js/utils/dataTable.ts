import { Badge, BadgeProps, ThemingProps } from "@chakra-ui/react";
import {
  AccessorFn,
  CellContext,
  ColumnDef,
  ColumnMeta,
  DeepKeys,
  DeepValue,
  FilterFn,
  createColumnHelper,
} from "@tanstack/react-table";
import * as _ from "lodash-es";
import React from "react";

import { SIMPLE_TIME_FORMAT } from "@/constants/datetime";

import useAuthStore from "@/stores/auth";

import { Dict } from "@/types";

import { formatCurrency } from "./currency";
import { formatDate, formatDateTime, formatTime, toDayjs } from "./datetime";

export type TableData = Dict;
type ColumnKey<T extends TableData> = DeepKeys<T>;

type DefaultBadgeProps = {
  type: "badge";
  label: string;
} & Omit<BadgeProps, "colorScheme" | "children">;

type RenderAsBadge<TValue = unknown> = TValue extends string | number | symbol
  ? DefaultBadgeProps & {
      colors: Record<TValue, ThemingProps["colorScheme"]>;
    }
  : TValue extends boolean
  ? DefaultBadgeProps & {
      colorScheme: ThemingProps["colorScheme"];
    }
  : never;

interface DataColumnOptions<TData extends TableData, TValue = unknown>
  extends ColumnMeta<TData, TValue> {
  label: string;
  id?: string;
  render?: (
    originalValue: TValue,
    value: string,
    context: CellContext<TData, TValue>,
  ) => React.ReactNode;
  renderAs?: (
    originalValue: TValue,
    value: string,
    context: CellContext<TData, TValue>,
  ) => RenderAsBadge<TValue>;
  filterable?: boolean;
  sortable?: boolean;
}

interface AccessorColumnOptions<TData extends TableData, TValue = unknown>
  extends DataColumnOptions<TData, TValue> {
  getValue: AccessorFn<TData, TValue>;
}

interface ColumnDefinitionHelper<TData extends TableData> {
  createData: <TKey extends ColumnKey<TData>>(
    key: TKey,
    options: DataColumnOptions<TData, DeepValue<TData, TKey>>,
  ) => ColumnDef<TData, DeepValue<TData, TKey>>;
  createAccessor: <TValue>(
    key: string,
    options: AccessorColumnOptions<TData, TValue>,
  ) => ColumnDef<TData, TValue>;
}

type ColumnDefinitionCallback<TData extends TableData> = (
  helper: ColumnDefinitionHelper<TData>,
) => ColumnDef<TData, any>[]; // eslint-disable-line @typescript-eslint/no-explicit-any

export const createColumnDefs = <TData extends TableData>(
  callback: ColumnDefinitionCallback<TData>,
) => {
  const columnHelper = createColumnHelper<TData>();

  const helper: ColumnDefinitionHelper<TData> = {
    createData: (
      key,
      {
        label,
        id,
        render,
        renderAs,
        filterable = true,
        sortable = true,
        ...options
      },
    ) =>
      columnHelper.accessor(key, {
        id: id || key.toString(),
        header: label,
        cell: (props) => {
          if (render) {
            return render(
              props.getValue(),
              transformValue(props.getValue(), options),
              props,
            );
          } else if (renderAs) {
            return renderDefaultComponent(
              props.getValue(),
              renderAs(
                props.getValue(),
                transformValue(props.getValue(), options),
                props,
              ),
            );
          }

          return transformValue(props.getValue(), options);
        },
        filterFn: getFilterFn(options),
        enableColumnFilter: filterable,
        enableGlobalFilter: filterable,
        enableSorting: sortable,
        meta: options,
      }),
    createAccessor: (
      key,
      {
        getValue,
        label,
        id,
        render,
        renderAs,
        filterable = true,
        sortable = true,
        ...options
      },
    ) =>
      columnHelper.accessor(getValue, {
        id: id || key,
        header: label,
        cell: (props) => {
          if (render) {
            return render(
              props.getValue(),
              transformValue(props.getValue(), options),
              props,
            );
          } else if (renderAs) {
            return renderDefaultComponent(
              props.getValue(),
              renderAs(
                props.getValue(),
                transformValue(props.getValue(), options),
                props,
              ),
            );
          }

          return transformValue(props.getValue(), options);
        },
        filterFn: getFilterFn(options),
        enableColumnFilter: filterable,
        enableGlobalFilter: filterable,
        enableSorting: sortable,
        meta: options,
      }),
  };

  return callback(helper);
};

const transformValue = <TData extends TableData, TValue = unknown>(
  value: TValue,
  meta: Partial<ColumnMeta<TData, TValue>>,
) => {
  const { currency, language } = useAuthStore.getState();
  const { display, dateFormat } = meta;

  if (
    display === "currency" &&
    (typeof value === "number" ||
      (typeof value === "string" && !Number.isNaN(Number(value))))
  ) {
    return formatCurrency(language, currency, Number(value));
  }

  if (
    display === "date" &&
    (typeof value === "string" || typeof value === "number")
  ) {
    return formatDate(value, dateFormat, false);
  }

  if (
    display === "localDate" &&
    (typeof value === "string" || typeof value === "number")
  ) {
    return formatDate(value, dateFormat, true);
  }

  if (
    display === "datetime" &&
    (typeof value === "string" || typeof value === "number")
  ) {
    return formatDateTime(value, dateFormat);
  }

  if (
    display === "time" &&
    (typeof value === "string" || typeof value === "number")
  ) {
    return formatTime(value, dateFormat);
  }

  return value || value === 0 ? `${value}` : "-";
};

const booleanFilterFn = <T extends TableData>() => {
  const filterFn: FilterFn<T> = (row, columnId, filterValue) => {
    return row.getValue(columnId) === filterValue;
  };

  filterFn.resolveFilterValue = (value) => {
    return value === "true";
  };

  return filterFn;
};

const listFilterFn = <TData extends TableData, TValue = unknown>(
  meta: Partial<ColumnMeta<TData, TValue>>,
) => {
  const filterFn: FilterFn<TData> = (row, columnId, filterValue) => {
    const { getOptionsValue } = meta;

    const value = row.getValue(columnId);
    const parsedFilterValue = JSON.parse(filterValue);

    if (!Array.isArray(parsedFilterValue) || !getOptionsValue) {
      return true;
    }

    return parsedFilterValue.some((v) => {
      if (!Array.isArray(value)) {
        return true;
      }

      return value.some((x) => getOptionsValue(x) === v);
    });
  };

  return filterFn;
};

const timeFilterFn = <T extends TableData>() => {
  const filterFn: FilterFn<T> = (row, columnId, filterValue) => {
    const datetime = toDayjs(row.getValue(columnId));
    return datetime.format(SIMPLE_TIME_FORMAT) === filterValue;
  };

  return filterFn;
};

const getFilterFn = <TData extends TableData, TValue = unknown>(
  meta: Partial<ColumnMeta<TData, TValue>>,
) => {
  const { display, filterKind } = meta;

  if (
    display === "currency" ||
    (display === "number" && filterKind !== "range") ||
    (!display && filterKind === "autocomplete")
  ) {
    return "weakEquals";
  }

  if (display === "boolean") {
    return booleanFilterFn<TData>();
  }

  if (display === "list") {
    return listFilterFn<TData, TValue>(meta);
  }

  if (display === "time") {
    return timeFilterFn<TData>();
  }

  return "auto";
};

const renderDefaultComponent = <TData extends TableData, TValue = unknown>(
  value: TValue,
  props: ReturnType<Required<DataColumnOptions<TData, TValue>>["renderAs"]>,
) => {
  if (value === null || value === undefined) {
    return null;
  }

  if (props.type === "badge") {
    return React.createElement(
      Badge,
      {
        variant: "solid",
        colorScheme:
          "colors" in props ? props.colors[value] : props.colorScheme,
        p: 1.5,
        rounded: "md",
        textTransform: "capitalize",
        ..._.omit(props, ["type", "colorScheme", "colors", "label"]),
      },
      props.label,
    );
  }

  return null;
};
