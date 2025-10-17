import { router } from "@inertiajs/react";
import { useQuery } from "@tanstack/react-query";

import { ApiResponse, Response } from "@/types/api";
import LeaveRegistration from "@/types/leaveRegistration";
import { QueryOptions } from "@/types/request";
import ScheduleEmployee from "@/types/scheduleEmployee";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getLeaveRegistrations = (
  options: Partial<RequestQueryStringOptions<LeaveRegistration>>,
) => {
  router.get("/leave-registrations" + createQueryString(options), undefined, {
    preserveState: true,
  });
};

export const useGetLeaveRegistrationSchedules = (
  leaveRegistrationId: number,
  options: QueryOptions<
    ScheduleEmployee,
    Response<ScheduleEmployee[]>,
    ApiResponse<ScheduleEmployee[]>
  > = {},
) => {
  const qs = createQueryString(options.request);
  const queryKey = [
    "web",
    "leave-registrations",
    leaveRegistrationId,
    "schedules",
    "json",
    qs,
  ];

  const query = useQuery({
    queryKey: queryKey,
    enabled: !!leaveRegistrationId,
    select: (response) => response.data,
    ...options.query,
  });

  return { ...query, queryKey };
};
