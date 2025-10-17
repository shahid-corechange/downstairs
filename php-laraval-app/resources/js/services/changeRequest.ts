import { router } from "@inertiajs/react";
import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import { ScheduleChangeRequest } from "@/types/schedule";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const useGetTotalUnhandledChangeRequests = () => {
  const qs = createQueryString<ScheduleChangeRequest>({
    size: -1,
    only: [],
    filter: { eq: { status: "pending" } },
  });

  return useQuery<Response<ScheduleChangeRequest[]>, unknown, number>({
    queryKey: ["web", "schedules", "change-requests", "json", qs],
    select: (response) => response.data.data.length,
  });
};

export const getChangeRequestHistories = (
  options: Partial<RequestQueryStringOptions<ScheduleChangeRequest>>,
) => {
  router.get(
    "/schedules/change-requests/histories" + createQueryString(options),
    undefined,
    {
      preserveState: true,
    },
  );
};
