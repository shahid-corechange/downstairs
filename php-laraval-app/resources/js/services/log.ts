import { router } from "@inertiajs/react";

import ActivityLog from "@/types/activityLog";
import AuthenticationLog from "@/types/authenticationLog";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getAuthLogs = (
  options: Partial<RequestQueryStringOptions<AuthenticationLog>>,
) => {
  router.get("/log/authentications" + createQueryString(options), undefined, {
    preserveState: true,
  });
};

export const getActivityLogs = (
  options: Partial<RequestQueryStringOptions<ActivityLog>>,
) => {
  router.get("/log/activities" + createQueryString(options), undefined, {
    preserveState: true,
  });
};
