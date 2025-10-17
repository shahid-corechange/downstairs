import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import { BlockDay } from "@/types/blockday";
import { QueryOptions } from "@/types/request";

import { createQueryString } from "@/utils/request";

export const useGetCashierBlockdays = (
  options: QueryOptions<BlockDay, Response<BlockDay[]>, BlockDay[]> = {},
) => {
  const qs = createQueryString(options.request, {
    filter: { eq: { categoryId: 1 } },
  });

  return useQuery({
    queryKey: ["web", "cashier", "blockdays", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};
