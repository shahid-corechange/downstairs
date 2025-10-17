import { router } from "@inertiajs/react";
import { useQuery } from "@tanstack/react-query";

import { ApiResponse, Response } from "@/types/api";
import Credit from "@/types/credit";
import Customer from "@/types/customer";
import Property from "@/types/property";
import { QueryOptions } from "@/types/request";
import { RutCoApplicant } from "@/types/rutCoApplicant";
import User from "@/types/user";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getCustomers = (
  options: Partial<RequestQueryStringOptions<User>>,
) => {
  router.get("/customers" + createQueryString(options), undefined, {
    preserveState: true,
  });
};

export const useGetCustomerProperties = (
  userId?: number,
  options: QueryOptions<Property, Response<Property[]>, Property[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "customers", userId, "properties", qs],
    enabled: !!userId,
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetCustomerAddresses = (
  userId?: number,
  options: QueryOptions<Customer, Response<Customer[]>, Customer[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "customers", userId, "addresses", qs],
    enabled: !!userId,
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetCustomerCredits = (
  userId?: number,
  options: QueryOptions<Credit, Response<Credit[]>, ApiResponse<Credit[]>> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "customers", userId, "credits", qs],
    enabled: !!userId,
    keepPreviousData: true,
    select: (response) => response.data,
    ...options.query,
  });
};

export const useGetCustomers = (
  options: QueryOptions<User, Response<User[]>, User[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "customers", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetCustomerRutCoApplicant = (
  userId?: number,
  options: QueryOptions<
    RutCoApplicant,
    Response<RutCoApplicant[]>,
    RutCoApplicant[]
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "customers", userId, "rut-co-applicants", qs],
    select: (response) => response.data.data,
    enabled: !!userId,
    ...options.query,
  });
};

export const useGetCustomersAddresses = (
  options: QueryOptions<Customer, Response<Customer[]>, Customer[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "customers", "addresses", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetCashierCustomers = (
  options: QueryOptions<User, Response<User[]>, User[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "cashier", "customers", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetCashierCustomerAddresses = (
  userId?: number,
  options: QueryOptions<Customer, Response<Customer[]>, Customer[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "cashier", "customers", userId, "addresses", qs],
    enabled: !!userId,
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};
