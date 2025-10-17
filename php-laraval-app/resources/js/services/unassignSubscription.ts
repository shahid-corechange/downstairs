import { router } from "@inertiajs/react";

import UnassignSubscription from "@/types/unassignSubscription";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getUnassignSubscriptions = (
  options: Partial<RequestQueryStringOptions<UnassignSubscription>>,
) => {
  router.get(
    "/unassign-subscriptions" + createQueryString(options),
    undefined,
    {
      preserveState: true,
    },
  );
};
