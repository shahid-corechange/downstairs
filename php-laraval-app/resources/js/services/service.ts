import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import { QueryOptions } from "@/types/request";
import Service from "@/types/service";

import { createQueryString } from "@/utils/request";

export const useGetServices = (
  options: QueryOptions<Service, Response<Service[]>, Service[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "services", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};
