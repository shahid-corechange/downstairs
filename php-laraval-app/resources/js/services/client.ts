import { createStandaloneToast } from "@chakra-ui/react";
import { QueryClient } from "@tanstack/react-query";
import axios from "axios";
import { t } from "i18next";

import { setError } from "@/hooks/error";

import { ErrorResponse, Response } from "@/types/api";

export const apiClient = axios.create({
  baseURL: "/api",
  headers: {
    "Content-Type": "application/json",
  },
});

export const webClient = axios.create({
  baseURL: "/",
  headers: {
    "Accept": "application/json",
    "Content-Type": "application/json",
  },
});

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      queryFn: async ({ queryKey }) => {
        const type = queryKey[0];
        const url = queryKey.slice(1).join("/");

        if (type === "api") {
          const response = await apiClient.get(url);
          return response;
        }

        const response = await webClient.get(url);
        return response;
      },
      refetchOnWindowFocus: false,
    },
  },
});

webClient.interceptors.response.use(
  (response: Response) => {
    if (
      ["GET", "HEAD", "OPTIONS"].includes(
        response.config.method?.toUpperCase() ?? "",
      )
    ) {
      return response;
    }

    if (response.data.message) {
      const { toast } = createStandaloneToast();

      toast({
        status: "success",
        variant: "solid",
        position: "top-right",
        title: t("success"),
        description: response.data.message,
        containerStyle: {
          fontSize: "sm",
        },
      });
    }

    return response;
  },
  (error: ErrorResponse) => {
    if (!error.response) {
      throw error;
    }

    if (
      ["GET", "HEAD", "OPTIONS"].includes(
        error.response.config.method?.toUpperCase() ?? "",
      )
    ) {
      return;
    }

    const { message } = error.response.data.error;
    const { toast } = createStandaloneToast();

    toast({
      status: "error",
      variant: "solid",
      position: "top-right",
      title: t("error"),
      description: message,
      containerStyle: {
        fontSize: "sm",
      },
    });

    setError(error);
    throw error;
  },
);
