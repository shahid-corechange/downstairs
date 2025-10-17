import { useQuery } from "@tanstack/react-query";

import { ApiResponse, Response } from "@/types/api";
import CustomerDiscount from "@/types/customerDiscount";
import { QueryOptions } from "@/types/request";

import { createQueryString } from "@/utils/request";

export const useGetCashierDiscounts = (
  options: QueryOptions<
    CustomerDiscount,
    Response<CustomerDiscount[]>,
    ApiResponse<CustomerDiscount[]>
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "cashier", "discounts", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data,
    ...options.query,
  });
};
