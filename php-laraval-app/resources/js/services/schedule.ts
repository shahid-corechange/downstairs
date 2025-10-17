import { useMutation, useQuery } from "@tanstack/react-query";
import { Dayjs } from "dayjs";

import { ApiResponse, Response } from "@/types/api";
import { QueryOptions } from "@/types/request";
import Schedule, {
  AddScheduleTaskPayload,
  AddScheduleWorkersPayload,
  BulkChangeWorkersPayload,
  CancelSchedulePayload,
  ChangeScheduleWorkerStatusPayload,
  CreateHistoricalSchedulePayload,
  DeleteScheduleTaskPayload,
  EditSchedulePayload,
  EditScheduleTaskPayload,
  RemoveScheduleWorkerPayload,
  ReschedulePayload,
  RevertScheduleWorkerPayload,
  ScheduleSummation,
} from "@/types/schedule";
import ScheduleEmployee from "@/types/scheduleEmployee";
import User from "@/types/user";

import { toDayjs } from "@/utils/datetime";
import { createQueryString } from "@/utils/request";

import { webClient } from "./client";

export const useGetSchedule = (
  scheduleId: number,
  options: QueryOptions<Schedule, Response<Schedule>, Schedule> = {},
) => {
  const qs = createQueryString<Schedule>(options.request);
  const queryKey = ["web", "schedules", scheduleId, "json", qs];

  const query = useQuery({
    queryKey: queryKey,
    enabled: !!scheduleId,
    select: (response) => response.data.data,
    ...options.query,
  });

  return { ...query, queryKey };
};

export const useGetAvailableWorkers = (
  startAt: string | Dayjs,
  endAt: string | Dayjs,
  excludedWorkerIds: number[] = [],
  options: QueryOptions<User, Response<User[]>, User[]> = {},
) => {
  let qs = createQueryString(options.request, { size: -1 });

  const utcStartAt = toDayjs(startAt).toISOString();
  const utcEndAt = toDayjs(endAt).toISOString();
  const excludedWorkerIdsQuery = excludedWorkerIds
    .map((id) => `workerIds[]=${id}`)
    .join("&");
  qs = `${qs}&startAt=${utcStartAt}&endAt=${utcEndAt}&${excludedWorkerIdsQuery}`;

  return useQuery({
    queryKey: ["web", "schedules", "workers", "available", qs],
    enabled: !!startAt && !!endAt,
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetScheduleWorkers = (
  scheduleId: number,
  options: QueryOptions<
    ScheduleEmployee,
    Response<ScheduleEmployee[]>,
    ScheduleEmployee[]
  > = {},
) => {
  const qs = createQueryString<ScheduleEmployee>(options.request, {
    filter: { eq: { scheduleId }, neq: { status: "cancel" } },
  });
  return useQuery({
    queryKey: ["web", "schedules", scheduleId, "workers", "json", qs],
    enabled: !!scheduleId,
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetScheduleWorker = (
  scheduleId: number,
  userId: number,
  options: QueryOptions<
    ScheduleEmployee,
    Response<ScheduleEmployee>,
    ScheduleEmployee
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "schedules", scheduleId, "workers", userId, "json", qs],
    enabled: !!scheduleId && !!userId,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetAllSchedules = (
  options: QueryOptions<
    Schedule,
    Response<Schedule[]>,
    ApiResponse<Schedule[]>
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "schedules", "json", qs],
    select: (response) => response.data,
    ...options.query,
  });
};

export const useGetAllWorkSchedules = (
  scheduleId: number,
  options: QueryOptions<
    ScheduleEmployee,
    Response<ScheduleEmployee[]>,
    ApiResponse<ScheduleEmployee[]>
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "schedules", scheduleId, "workers", "json", qs],
    select: (response) => response.data,
    ...options.query,
  });
};

export const useGetAllScheduleWorkers = (
  scheduleId: number,
  options: QueryOptions<
    ScheduleEmployee,
    Response<ScheduleEmployee[]>,
    ScheduleEmployee[]
  > = {},
) => {
  const qs = createQueryString<ScheduleEmployee>(options.request);

  return useQuery({
    queryKey: ["web", "schedules", scheduleId, "workers", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useScheduleSummation = (dateBetween: [string, string]) => {
  const qs: string = createQueryString<Schedule>({
    size: -1,
    include: ["detail.subscription.service", "items", "items.item", "property"],
    only: [
      "startAt",
      "detail.subscription.service.name",
      "property.squareMeter",
      "items.item.name",
      "items.quantity",
    ],
    filter: { between: { startAt: dateBetween } },
  });

  return useQuery<Response<Schedule[]>, unknown, ScheduleSummation[]>({
    queryKey: ["web", "schedules", "json", qs],
    select: (response) => {
      const { data } = response.data;

      const groupedServiceSummation = data.reduce<
        Record<string, ScheduleSummation>
      >((acc, item) => {
        const serviceName = item?.service?.name ?? "";
        const area = item.property?.squareMeter ?? 0;
        const serviceSummation = acc[serviceName];

        if (!serviceSummation) {
          acc[serviceName] = {
            type: serviceName,
            amount: 1,
            size: area,
            unit: "m²",
          };
        } else {
          serviceSummation.amount += 1;
          if (serviceSummation.size) {
            serviceSummation.size += area;
          }
        }

        return acc;
      }, {});

      const groupedItemSummation = data.reduce<
        Record<string, ScheduleSummation>
      >((acc, item) => {
        const items = item?.items ?? [];
        items.forEach(({ item, quantity }) => {
          const itemSummation = acc[item?.name ?? ""];
          if (!itemSummation) {
            acc[item?.name ?? ""] = {
              type: item?.name ?? "",
              amount: quantity,
            };
          } else {
            itemSummation.amount += quantity;
          }
        });

        return acc;
      }, {});

      return [
        ...Object.values(groupedServiceSummation),
        ...Object.values(groupedItemSummation),
      ];
    },
  });
};

export const useRescheduleMutation = () =>
  useMutation({
    mutationFn: async ({ scheduleId, ...payload }: ReschedulePayload) => {
      const response = await webClient.post<ApiResponse<Schedule>>(
        `/schedules/${scheduleId}/reschedule`,
        payload,
      );

      return {
        response,
        data: response.data.data,
      };
    },
  });

export const useCancelScheduleMutation = () =>
  useMutation({
    mutationFn: async ({ scheduleId, ...payload }: CancelSchedulePayload) => {
      const response = await webClient.post<ApiResponse<Schedule>>(
        `/schedules/${scheduleId}/cancel`,
        payload,
      );

      return {
        response,
        data: response.data.data,
      };
    },
  });

export const useEditScheduleMutation = () =>
  useMutation({
    mutationFn: async ({ scheduleId, ...payload }: EditSchedulePayload) => {
      const response = await webClient.patch<ApiResponse<Schedule>>(
        `/schedules/${scheduleId}`,
        payload,
      );

      return {
        response,
        data: response.data.data,
      };
    },
  });

export const useAddScheduleWorkersMutation = () =>
  useMutation({
    mutationFn: async ({
      scheduleId,
      ...payload
    }: AddScheduleWorkersPayload) => {
      const response = await webClient.post<ApiResponse<Schedule>>(
        `/schedules/${scheduleId}/workers`,
        payload,
      );

      return {
        response,
        data: response.data.data,
      };
    },
  });

export const useChangeScheduleWorkerStatusMutation = () =>
  useMutation({
    mutationFn: async ({
      scheduleId,
      userId,
      action,
    }: ChangeScheduleWorkerStatusPayload) => {
      const response = await webClient.request<ApiResponse<Schedule>>({
        url: `/schedules/${scheduleId}/workers/${userId}/${action}`,
        method: action === "enable" ? "post" : "delete",
      });

      return {
        response,
        data: response.data.data,
      };
    },
  });

export const useRemoveScheduleWorkerMutation = () =>
  useMutation({
    mutationFn: async ({ scheduleId, userId }: RemoveScheduleWorkerPayload) => {
      const response = await webClient.delete<ApiResponse<Schedule>>(
        `/schedules/${scheduleId}/workers/${userId}`,
      );

      return {
        response,
        data: response.data.data,
      };
    },
  });

export const useRevertScheduleWorkerMutation = () =>
  useMutation({
    mutationFn: async ({ scheduleId, userId }: RevertScheduleWorkerPayload) => {
      const response = await webClient.post<ApiResponse<Schedule>>(
        `/schedules/${scheduleId}/workers/${userId}/revert`,
      );

      return {
        response,
        data: response.data.data,
      };
    },
  });

export const useAddScheduleTaskMutation = () =>
  useMutation({
    mutationFn: async ({ scheduleId, ...payload }: AddScheduleTaskPayload) => {
      const response = await webClient.post<ApiResponse<Schedule>>(
        `/schedules/${scheduleId}/tasks`,
        payload,
      );

      return {
        response,
        data: response.data.data,
      };
    },
  });

export const useEditScheduleTaskMutation = () =>
  useMutation({
    mutationFn: async ({
      scheduleId,
      taskId,
      ...payload
    }: EditScheduleTaskPayload) => {
      const response = await webClient.patch<ApiResponse<Schedule>>(
        `/schedules/${scheduleId}/tasks/${taskId}`,
        payload,
      );

      return {
        response,
        data: response.data.data,
      };
    },
  });

export const useDeleteScheduleTaskMutation = () =>
  useMutation({
    mutationFn: async ({ scheduleId, taskId }: DeleteScheduleTaskPayload) => {
      const response = await webClient.delete<ApiResponse<Schedule>>(
        `/schedules/${scheduleId}/tasks/${taskId}`,
      );

      return {
        response,
        data: response.data.data,
      };
    },
  });

export const useCreateHistoricalScheduleMutation = () =>
  useMutation({
    mutationFn: async (payload: CreateHistoricalSchedulePayload) => {
      const response = await webClient.post<ApiResponse<Schedule>>(
        "/schedules/history",
        payload,
      );

      return {
        response,
        data: response.data.data,
      };
    },
  });

export const useBulkChangeWorkersMutation = () =>
  useMutation({
    mutationFn: async (payload: BulkChangeWorkersPayload) => {
      await webClient.post("/schedules/workers/bulk-change", payload);
    },
  });
