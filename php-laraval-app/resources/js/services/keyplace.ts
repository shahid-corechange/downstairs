import { router } from "@inertiajs/react";
import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import KeyPlace from "@/types/keyplace";
import { QueryOptions } from "@/types/request";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getKeyPlaces = (
  options: Partial<RequestQueryStringOptions<KeyPlace>>,
) => {
  router.get("/keyplaces" + createQueryString(options), undefined, {
    preserveState: true,
  });
};

export const useGetKeyPlaces = (
  options: QueryOptions<KeyPlace, Response<KeyPlace[]>, KeyPlace[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "keyplaces", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};
