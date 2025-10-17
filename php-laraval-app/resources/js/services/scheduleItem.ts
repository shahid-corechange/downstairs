import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import { QueryOptions } from "@/types/request";
import { ScheduleItem } from "@/types/schedule";

import { createQueryString } from "@/utils/request";

export const useGetScheduleItems = (
  options: QueryOptions<
    ScheduleItem,
    Response<ScheduleItem[]>,
    ScheduleItem[]
  > = {},
) => {
  const qs = createQueryString<ScheduleItem>(options.request);

  return useQuery({
    queryKey: ["web", "schedules", "items", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};
