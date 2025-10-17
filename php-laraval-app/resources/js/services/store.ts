import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import { QueryOptions } from "@/types/request";
import { Store } from "@/types/store";

import { createQueryString } from "@/utils/request";

export const useGetStores = (
  options: QueryOptions<Store, Response<Store[]>, Store[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "stores", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetStore = (
  storeId?: number,
  options: QueryOptions<Store, Response<Store>, Store> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "stores", storeId, "json", qs],
    enabled: !!storeId,
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetCashierStores = (
  options: QueryOptions<Store, Response<Store[]>, Store[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "cashier", "stores", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};
