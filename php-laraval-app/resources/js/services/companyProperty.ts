import { router } from "@inertiajs/react";

import Property from "@/types/property";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getCompaniesProperties = (
  options: Partial<RequestQueryStringOptions<Property>>,
) => {
  router.get("/companies/properties" + createQueryString(options), undefined, {
    preserveState: true,
  });
};
