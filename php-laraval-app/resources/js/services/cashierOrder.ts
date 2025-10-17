import { useQuery } from "@tanstack/react-query";

import { ApiResponse, Response } from "@/types/api";
import { LaundryOrder } from "@/types/laundryOrder";
import { QueryOptions } from "@/types/request";
import Schedule from "@/types/schedule";

import { createQueryString } from "@/utils/request";

export const useGetCashierOrders = (
  options: QueryOptions<
    LaundryOrder,
    Response<LaundryOrder[]>,
    ApiResponse<LaundryOrder[]>
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "cashier", "orders", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data,
    ...options.query,
  });
};

export const useGetCustomerSchedules = (
  options: QueryOptions<
    Schedule,
    Response<Schedule[]>,
    ApiResponse<Schedule[]>
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "cashier", "schedules", "json", qs],
    select: (response) => response.data,
    ...options.query,
  });
};
