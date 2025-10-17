import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import City from "@/types/city";
import { QueryOptions } from "@/types/request";

import { createQueryString } from "@/utils/request";

export const useGetCityByCountryService = (
  countryId: number,
  options: QueryOptions<City, Response<City[]>, City[]> = {},
) => {
  const qs = createQueryString(options.request, {
    filter: { eq: { countryId } },
    size: -1,
  });

  return useQuery({
    queryKey: ["api", "v0", "cities", qs],
    enabled: countryId > 0,
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};
