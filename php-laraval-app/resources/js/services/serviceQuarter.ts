import { router } from "@inertiajs/react";

import ServiceQuarter from "@/types/serviceQuarter";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getServiceQuarter = (
  options: Partial<RequestQueryStringOptions<ServiceQuarter>>,
) => {
  router.get("/serviceQuarter" + createQueryString(options), undefined, {
    preserveState: true,
  });
};
