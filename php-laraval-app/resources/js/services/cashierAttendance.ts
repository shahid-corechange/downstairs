import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import CashierAttendance from "@/types/cashierAttendance";
import { QueryOptions } from "@/types/request";

import { createQueryString } from "@/utils/request";

export const useGetCashierAttendances = (
  options: QueryOptions<
    CashierAttendance,
    Response<CashierAttendance[]>,
    CashierAttendance[]
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "cashier", "attendances", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetAllCashierAttendances = (
  options: QueryOptions<
    CashierAttendance,
    Response<CashierAttendance[]>,
    CashierAttendance[]
  > = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "cashier-attendances", "json", qs],
    keepPreviousData: true,
    select: (response) => response.data.data,
    ...options.query,
  });
};
