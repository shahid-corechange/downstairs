import { router } from "@inertiajs/react";
import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import { QueryOptions } from "@/types/request";
import Subscription from "@/types/subscription";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getCompaniesSubscriptions = (
  options: Partial<RequestQueryStringOptions<Subscription>>,
) => {
  router.get(
    "/companies/subscriptions" + createQueryString(options),
    undefined,
    {
      preserveState: true,
    },
  );
};

export const useGetCompanySubscriptions = (
  options: QueryOptions<
    Subscription,
    Response<Subscription[]>,
    Subscription[]
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "companies", "subscriptions", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetCompanySubscription = (
  subscriptionId?: number,
  options: QueryOptions<
    Subscription,
    Response<Subscription>,
    Subscription
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "companies", "subscriptions", subscriptionId, "json", qs],
    enabled: !!subscriptionId,
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};
