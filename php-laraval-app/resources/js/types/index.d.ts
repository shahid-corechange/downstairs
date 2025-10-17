import { PagePagination } from "./pagination";
import User from "./user";

export interface FlashData<TSuccessPayload = unknown, TErrorPayload = unknown> {
  success?: string;
  successPayload?: TSuccessPayload;
  error?: string;
  errorPayload?: TErrorPayload;
}

export type PageProps<
  T extends Record<string, unknown> = unknown,
  TSuccessPayload = unknown,
  TErrorPayload = unknown,
> = T & {
  user: User;
  flash: FlashData<TSuccessPayload, TErrorPayload>;
  filter: PageFilter;
  sort?: Record<string, "asc" | "desc">;
  query?: Record<string, string>;
  storeId?: number | null;
  stores: Store[];
};

export type PaginatedPageProps<T extends Record<string, unknown> = unknown> =
  PageProps<T> & {
    pagination: PagePagination;
  };

export type PageFilter = {
  filters: PageFilterItem[];
  orFilters: PageFilterItem[][];
  exact: boolean;
};

export type PageFilterItem = {
  key: string;
  criteria: string;
  value: string | boolean;
};

export type Primitive = string | number | boolean;
export type InputValue = string | number | readonly string[] | undefined;

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export type Dict<T = any> = Record<string, T>;
