import { UseQueryOptions, useQuery } from "@tanstack/react-query";

import AddonStatistic from "@/types/addonStatistic";
import { Response } from "@/types/api";

export const useAddonStatistic = (
  startAt: string,
  endAt: string,
  options: UseQueryOptions<
    Response<AddonStatistic[]>,
    unknown,
    AddonStatistic[]
  > = {},
) => {
  return useQuery({
    queryKey: [
      "web",
      "dashboard",
      "widget",
      "addons",
      "statistic",
      `?startAt=${startAt}&endAt=${endAt}`,
    ],
    keepPreviousData: true,
    select: (response) => response.data.data,
    staleTime: Infinity,
    ...options,
  });
};
