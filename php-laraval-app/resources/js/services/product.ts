import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import Product from "@/types/product";
import { QueryOptions } from "@/types/request";

import { createQueryString } from "@/utils/request";

export const useGetProducts = (
  options: QueryOptions<Product, Response<Product[]>, Product[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "products", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};
