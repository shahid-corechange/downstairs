import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import Country from "@/types/country";
import { QueryOptions } from "@/types/request";

import { createQueryString } from "@/utils/request";

export const useGetCountries = (
  options: QueryOptions<Country, Response<Country[]>, Country[]> = {},
) => {
  const qs = createQueryString(options.request, {
    size: -1,
  });

  return useQuery({
    queryKey: ["api", "v0", "countries", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    staleTime: Infinity,
    ...options.query,
  });
};
