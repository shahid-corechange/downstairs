import { router } from "@inertiajs/react";
import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import Customer from "@/types/customer";
import { QueryOptions } from "@/types/request";
import User from "@/types/user";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getCompanies = (
  options: Partial<RequestQueryStringOptions<Customer>>,
) => {
  router.get("/companies" + createQueryString(options), undefined, {
    preserveState: true,
  });
};

export const useGetCompanyAddresses = (
  companyId?: number,
  options: QueryOptions<Customer, Response<Customer[]>, Customer[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "companies", companyId, "addresses", qs],
    enabled: !!companyId,
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetCompanyUsers = (
  options: QueryOptions<User, Response<User[]>, User[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "companies", "users", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetCompaniesAddresses = (
  options: QueryOptions<Customer, Response<Customer[]>, Customer[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "companies", "addresses", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};
