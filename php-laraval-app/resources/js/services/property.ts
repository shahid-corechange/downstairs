import { router } from "@inertiajs/react";

import Property from "@/types/property";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getProperties = (
  options: Partial<RequestQueryStringOptions<Property>>,
) => {
  router.get("/customers/properties" + createQueryString(options), undefined, {
    preserveState: true,
  });
};
