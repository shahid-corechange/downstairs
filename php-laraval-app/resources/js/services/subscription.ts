import { router } from "@inertiajs/react";
import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import { QueryOptions } from "@/types/request";
import Subscription from "@/types/subscription";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getSubscriptions = (
  options: Partial<RequestQueryStringOptions<Subscription>>,
) => {
  router.get(
    "/customers/subscriptions" + createQueryString(options),
    undefined,
    {
      preserveState: true,
    },
  );
};

export const useGetSubscriptions = (
  options: QueryOptions<
    Subscription,
    Response<Subscription[]>,
    Subscription[]
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "customers", "subscriptions", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetSubscription = (
  subscriptionId?: number,
  options: QueryOptions<
    Subscription,
    Response<Subscription>,
    Subscription
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "customers", "subscriptions", subscriptionId, "json", qs],
    enabled: !!subscriptionId,
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const getSubscriptionTypes = (fixedPriceType: string): string[] => {
  switch (fixedPriceType) {
    case "laundry":
      return ["App\\Models\\SubscriptionLaundryDetail"];
    case "cleaning":
      return ["App\\Models\\SubscriptionCleaningDetail"];
    default:
      return [
        "App\\Models\\SubscriptionLaundryDetail",
        "App\\Models\\SubscriptionCleaningDetail",
      ];
  }
};
