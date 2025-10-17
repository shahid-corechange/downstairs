import { router } from "@inertiajs/react";
import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import Order from "@/types/order";
import { QueryOptions } from "@/types/request";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getOrders = (
  options: Partial<RequestQueryStringOptions<Order>>,
) => {
  router.get("/orders" + createQueryString(options), undefined, {
    preserveState: true,
  });
};

export const useGetOrder = (
  orderId?: number,
  options: QueryOptions<Order, Response<Order>, Order> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "orders", orderId, "json", qs],
    enabled: !!orderId,
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};
