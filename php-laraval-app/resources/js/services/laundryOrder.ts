import { router } from "@inertiajs/react";
import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import { LaundryOrder } from "@/types/laundryOrder";
import { QueryOptions } from "@/types/request";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getLaundryOrders = (
  options: Partial<RequestQueryStringOptions<LaundryOrder>>,
) => {
  router.get("/laundry-orders" + createQueryString(options), undefined, {
    preserveState: true,
  });
};

export const useGetLaundryOrder = (
  laundryOrderId?: number,
  options: QueryOptions<
    LaundryOrder,
    Response<LaundryOrder>,
    LaundryOrder
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "laundry-orders", laundryOrderId, "json", qs],
    enabled: !!laundryOrderId,
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetLaundryOrders = (
  options: QueryOptions<
    LaundryOrder,
    Response<LaundryOrder[]>,
    LaundryOrder[]
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "laundry-orders", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetCashierLaundryOrders = (
  options: QueryOptions<
    LaundryOrder,
    Response<LaundryOrder[]>,
    LaundryOrder[]
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "cashier", "orders", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};
