import { router } from "@inertiajs/react";

import PriceAdjustment from "@/types/priceAdjustment";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getPriceAdjustments = (
  options: Partial<RequestQueryStringOptions<PriceAdjustment>>,
) => {
  router.get("price-adjustments" + createQueryString(options), undefined, {
    preserveState: true,
  });
};
