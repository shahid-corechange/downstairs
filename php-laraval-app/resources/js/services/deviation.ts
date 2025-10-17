import { router } from "@inertiajs/react";
import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import Deviation from "@/types/deviation";
import { QueryOptions } from "@/types/request";
import ScheduleDeviation from "@/types/scheduleDeviation";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getDeviations = (
  options: Partial<RequestQueryStringOptions<ScheduleDeviation>>,
) => {
  router.get("/deviations" + createQueryString(options), undefined, {
    preserveState: true,
  });
};

export const getEmployeesDeviations = (
  options: Partial<RequestQueryStringOptions<Deviation>>,
) => {
  router.get("/deviations/employee" + createQueryString(options), undefined, {
    preserveState: true,
  });
};

export const useGetDeviation = (
  deviationId?: number,
  options: QueryOptions<
    ScheduleDeviation,
    Response<ScheduleDeviation>,
    ScheduleDeviation
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "deviations", deviationId, "json", qs],
    enabled: !!deviationId,
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetEmployeeDeviations = (
  options: QueryOptions<Deviation, Response<Deviation[]>, Deviation[]> = {},
) => {
  const qs = createQueryString<Deviation>(options.request);

  return useQuery({
    queryKey: ["web", "deviations", "employee", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetEmployeeDeviationsBySchedule = (
  scheduleId: number,
  options: QueryOptions<Deviation, Response<Deviation[]>, Deviation[]> = {},
) => {
  const qs = createQueryString<Deviation>(options.request, {
    filter: { eq: { scheduleId } },
  });

  return useQuery({
    queryKey: ["web", "deviations", "employee", "json", qs],
    enabled: !!scheduleId,
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetTotalUnhandledDeviations = () => {
  const qs = createQueryString<Deviation>({
    size: -1,
    only: [],
    filter: { eq: { isHandled: false } },
  });

  return useQuery<Response<Deviation[]>, unknown, number>({
    queryKey: ["web", "deviations", "json", qs],
    select: (response) => response.data.data.length,
  });
};

export const useGetTotalUnhandledEmployeeDeviations = () => {
  const qs = createQueryString<Deviation>({
    size: -1,
    only: [],
    filter: { eq: { isHandled: false } },
  });

  return useQuery<Response<Deviation[]>, unknown, number>({
    queryKey: ["web", "deviations", "employee", "json", qs],
    select: (response) => response.data.data.length,
  });
};
