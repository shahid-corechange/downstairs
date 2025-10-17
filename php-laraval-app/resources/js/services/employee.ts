import { router } from "@inertiajs/react";

import User from "@/types/user";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getEmployees = (
  options: Partial<RequestQueryStringOptions<User>>,
) => {
  router.get("/employees" + createQueryString(options), undefined, {
    preserveState: true,
  });
};
