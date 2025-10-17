import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import Employee from "@/types/employee";
import { QueryOptions } from "@/types/request";
import { Store } from "@/types/store";

import { createQueryString } from "@/utils/request";

export const useGetEmployeeStores = (
  options: QueryOptions<Store, Response<Store[]>, Store[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "stores", "json", qs],
    keepPreviousData: true,
    refetchOnMount: false,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetEmployees = (
  options: QueryOptions<Employee, Response<Employee[]>, Employee[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "cashier", "employees", "json", qs],
    keepPreviousData: true,
    refetchOnMount: false,
    select: (response) => response.data.data,
    ...options.query,
  });
};
