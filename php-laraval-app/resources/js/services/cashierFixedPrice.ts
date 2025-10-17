import { useQuery } from "@tanstack/react-query";

import { ApiResponse, Response } from "@/types/api";
import FixedPrice from "@/types/fixedPrice";
import { QueryOptions } from "@/types/request";

import { createQueryString } from "@/utils/request";

export const useGetCashierFixedPrice = (
  userId?: number,
  options: QueryOptions<
    FixedPrice,
    Response<FixedPrice>,
    ApiResponse<FixedPrice>
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "cashier", "users", userId, "fixed-price", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data,
    ...options.query,
  });
};
