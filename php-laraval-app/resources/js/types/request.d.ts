import { QueryKey, UseQueryOptions } from "@tanstack/react-query";

import { RequestQueryStringOptions } from "@/utils/request";

export interface QueryOptions<
  TModel = unknown,
  TQueryFnData = unknown,
  TData = TQueryFnData,
  TError = unknown,
  TQueryKey extends QueryKey = QueryKey,
> {
  request?: Partial<RequestQueryStringOptions<TModel>>;
  query?: Omit<
    UseQueryOptions<TQueryFnData, TError, TData, TQueryKey>,
    "queryFn" | "initialData"
  > & { initialData?: () => undefined };
}
