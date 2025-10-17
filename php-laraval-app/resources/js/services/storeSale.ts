import { router } from "@inertiajs/react";
import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import { QueryOptions } from "@/types/request";
import StoreSale from "@/types/storeSale";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getStoreSales = (
  options: Partial<RequestQueryStringOptions<StoreSale>>,
) => {
  router.get(
    "/cashier/direct-sales/histories" + createQueryString(options),
    undefined,
    {
      preserveState: true,
    },
  );
};
export const useGetStoreSales = (
  options: QueryOptions<StoreSale, Response<StoreSale[]>, StoreSale[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "cashier", "direct-sales", "histories", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetStoreSale = (
  storeSaleId?: number,
  options: QueryOptions<StoreSale, Response<StoreSale>, StoreSale> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: [
      "web",
      "cashier",
      "direct-sales",
      "histories",
      storeSaleId,
      "json",
      qs,
    ],
    enabled: !!storeSaleId,
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};
