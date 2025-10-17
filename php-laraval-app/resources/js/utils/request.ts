import * as _ from "lodash-es";

import { LooseDeepKeys } from "@/types/utils";

import { Primitive } from "@/types";

import { sortedObjectEntries } from "./object";

export type RequestQueryStringFilter<T> = {
  eq: Partial<Record<LooseDeepKeys<T>, Primitive>>;
  between: Partial<Record<LooseDeepKeys<T>, [Primitive, Primitive]>>;
  gt: Partial<Record<LooseDeepKeys<T>, Primitive>>;
  gte: Partial<Record<LooseDeepKeys<T>, Primitive>>;
  in: Partial<Record<LooseDeepKeys<T>, Primitive[]>>;
  like: Partial<Record<LooseDeepKeys<T>, Primitive>>;
  lt: Partial<Record<LooseDeepKeys<T>, Primitive>>;
  lte: Partial<Record<LooseDeepKeys<T>, Primitive>>;
  neq: Partial<Record<LooseDeepKeys<T>, Primitive>>;
  notIn: Partial<Record<LooseDeepKeys<T>, Primitive[]>>;
  nullable: Partial<Record<LooseDeepKeys<T>, boolean>>;
};

export interface RequestQueryStringOptions<T> {
  filter: Partial<RequestQueryStringFilter<T>>;
  orFilters: Partial<RequestQueryStringFilter<T>>[];
  exactFilter: boolean;
  page: number;
  pagination: "page" | "cursor";
  show: "all" | "deleted" | "active";
  cursor: string;
  size: number;
  sort: Partial<Record<LooseDeepKeys<T>, "asc" | "desc">>;
  groupBy: keyof T;
  include: LooseDeepKeys<T>[];
  exclude: LooseDeepKeys<T>[];
  except: LooseDeepKeys<T>[];
  only: LooseDeepKeys<T>[];
}

const constructFilterQueryString = <T>(
  filter: Partial<RequestQueryStringFilter<T>>,
) => {
  const params: string[] = [];

  for (const [key, value] of sortedObjectEntries(filter)) {
    if (!value && value !== 0) {
      continue;
    }

    if (["between", "in", "notIn"].includes(key)) {
      for (const [field, fieldValue] of sortedObjectEntries(value)) {
        if (!fieldValue || fieldValue.length === 0) {
          continue;
        }

        params.push(`${field}.${key}=${fieldValue.join(",")}`);
      }

      continue;
    }

    if (
      ["eq", "gt", "gte", "like", "lt", "lte", "neq", "nullable"].includes(key)
    ) {
      for (const [field, fieldValue] of sortedObjectEntries(value)) {
        if (!fieldValue && fieldValue !== 0 && fieldValue !== false) {
          continue;
        }

        params.push(`${field}.${key}=${fieldValue}`);
      }

      continue;
    }

    params.push(`${key}=${value}`);
  }

  return params;
};

const constructQueryString = <T>({
  filter,
  orFilters,
  exactFilter,
  sort,
  include,
  exclude,
  except,
  only,
  ...options
}: Partial<RequestQueryStringOptions<T>>) => {
  const params: string[] = [];

  if (filter) {
    params.push(...constructFilterQueryString(filter));
  }

  if (orFilters && orFilters.length > 0) {
    for (const orFilter of orFilters) {
      const orFilterParams = constructFilterQueryString(orFilter).join("|");
      params.push(`or[]=${orFilterParams}`);
    }
  }

  if (exactFilter) {
    params.push("filter=exact");
  }

  if (sort && Object.keys(sort).length > 0) {
    const sortParams = [];

    for (const [field, order] of Object.entries(sort)) {
      sortParams.push(`${field}.${order}`);
    }

    params.push(`sort=${sortParams.join(",")}`);
  }

  if (include && include.length > 0) {
    params.push(`include=${include.join(",")}`);
  }

  if (exclude && exclude.length > 0) {
    params.push(`exclude=${exclude.join(",")}`);
  }

  if (except && except.length > 0) {
    params.push(`except=${except.join(",")}`);
  }

  if (only && only.length > 0) {
    params.push(`only=${only.join(",")}`);
  }

  for (const [key, value] of sortedObjectEntries(options)) {
    if (value !== undefined) {
      params.push(`${key}=${value}`);
    }
  }

  return `?${params.join("&")}`;
};

export const createQueryString = <T>(
  options: Partial<RequestQueryStringOptions<T>> = {},
  fixedOptions: Partial<RequestQueryStringOptions<T>> = {},
) => {
  return constructQueryString(_.merge(options, fixedOptions));
};
