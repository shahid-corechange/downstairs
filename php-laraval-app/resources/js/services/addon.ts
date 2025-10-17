import { useQuery } from "@tanstack/react-query";

import Addon from "@/types/addon";
import { Response } from "@/types/api";
import { QueryOptions } from "@/types/request";

import { createQueryString } from "@/utils/request";

export const useGetAddons = (
  options: QueryOptions<Addon, Response<Addon[]>, Addon[]> = {},
) => {
  const qs = createQueryString(options.request, {
    filter: { eq: { categoryId: 1 } },
  });

  return useQuery({
    queryKey: ["web", "addons", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};
