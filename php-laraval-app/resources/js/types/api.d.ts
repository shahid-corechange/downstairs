import { AxiosError, AxiosResponse } from "axios";

import { CursorPagination, PagePagination } from "./pagination";

export interface ApiResponse<T = unknown> {
  data: T;
  message?: string;
  apiVersion?: string;
  pagination?: PagePagination | CursorPagination;
  meta?: Record<string, string>;
}

export type Response<T = unknown> = AxiosResponse<ApiResponse<T>>;

export interface ApiErrorDetailResponse<T = unknown> {
  code: number;
  message: string;
  errors: Record<string, T>;
}

export interface ApiErrorResponse<T = unknown> {
  error: ApiErrorDetailResponse<T>;
}

export type ErrorResponse<T = unknown> = AxiosError<ApiErrorResponse<T>>;
