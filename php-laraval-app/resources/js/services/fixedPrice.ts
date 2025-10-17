import { router } from "@inertiajs/react";
import { useQuery } from "@tanstack/react-query";

import { ApiResponse, Response } from "@/types/api";
import FixedPrice from "@/types/fixedPrice";
import { QueryOptions } from "@/types/request";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getFixedPrices = (
  options: Partial<RequestQueryStringOptions<FixedPrice>>,
) => {
  router.get("/customers/fixedprices" + createQueryString(options), undefined, {
    preserveState: true,
  });
};

export const useGetFixedPrice = (
  fixedPriceId?: number,
  options: QueryOptions<FixedPrice, Response<FixedPrice>, FixedPrice> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "customers", "fixedprices", fixedPriceId, "json", qs],
    enabled: !!fixedPriceId,
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetCustomerFixedPrices = (
  options: QueryOptions<
    FixedPrice,
    Response<FixedPrice[]>,
    ApiResponse<FixedPrice[]>
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "customers", "fixedprices", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data,
    ...options.query,
  });
};

export const useGetAllFixedPrices = (
  options: QueryOptions<FixedPrice, Response<FixedPrice[]>, FixedPrice[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "customers", "fixedprices", "all", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};
