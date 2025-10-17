import { router } from "@inertiajs/react";
import { useQuery } from "@tanstack/react-query";

import { ApiResponse, Response } from "@/types/api";
import MonthlyTimeReport from "@/types/monthlyTimeReport";
import { QueryOptions } from "@/types/request";
import TimeAdjustment from "@/types/timeAdjustment";
import TimeReport from "@/types/timeReport";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getTimeReports = (
  options: Partial<RequestQueryStringOptions<TimeReport>>,
) => {
  router.get("/time-reports/daily" + createQueryString(options), undefined, {
    preserveState: true,
  });
};

export const useGetWorkHours = (
  options: QueryOptions<TimeReport, Response<TimeReport[]>, TimeReport[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "time-reports", "daily", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const getMonthlyTimeReports = (
  options: Partial<RequestQueryStringOptions<MonthlyTimeReport>>,
) => {
  router.get("/time-reports" + createQueryString(options), undefined, {
    preserveState: true,
  });
};

export const useGetTimeAdjustments = (
  options: QueryOptions<
    TimeAdjustment,
    Response<TimeAdjustment[]>,
    ApiResponse<TimeAdjustment[]>
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "time-adjustments", "json", qs],
    select: (response) => response.data,
    ...options.query,
  });
};
