import { router } from "@inertiajs/react";

import Feedback from "@/types/feedback";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getFeedbacks = (
  options: Partial<RequestQueryStringOptions<Feedback>>,
) => {
  router.get("/feedbacks" + createQueryString(options), undefined, {
    preserveState: true,
  });
};
