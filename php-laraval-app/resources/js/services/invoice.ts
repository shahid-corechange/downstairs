import { router } from "@inertiajs/react";
import { useQuery } from "@tanstack/react-query";

import { Response } from "@/types/api";
import Invoice from "@/types/invoice";
import { QueryOptions } from "@/types/request";

import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

export const getInvoices = (
  options: Partial<RequestQueryStringOptions<Invoice>>,
) => {
  router.get("/invoices" + createQueryString(options), undefined, {
    preserveState: true,
  });
};

export const useGetInvoices = (
  options: QueryOptions<Invoice, Response<Invoice[]>, Invoice[]> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "invoices", "json", qs],
    select: (response) => response.data.data,
    ...options.query,
  });
};

export const useGetInvoice = (
  invoiceId?: number,
  options: QueryOptions<Invoice, Response<Invoice>, Invoice> = {},
) => {
  const qs = createQueryString(options.request);

  return useQuery({
    queryKey: ["web", "invoices", invoiceId, "json", qs],
    enabled: !!invoiceId,
    select: (response) => response.data.data,
    ...options.query,
  });
};
