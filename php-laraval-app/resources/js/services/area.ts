import { useQuery } from "@tanstack/react-query";
import axios, { AxiosError } from "axios";

import { HTTP_NOT_FOUND } from "@/constants/response";

import { AddressArea } from "@/types/address";
import { ApiResponse } from "@/types/api";

import { apiClient } from "./client";

interface GeonamesPostalCodeApiResponse {
  total_count: number;
  results: {
    place_name: string;
  }[];
}

export const useGetAreaByPostalCode = (
  postalCode: string,
  countryCode?: string,
) => {
  return useQuery<Partial<AddressArea>>({
    queryKey: ["api", "v0", "areas", postalCode],
    queryFn: async () => {
      try {
        const response = await apiClient.get<ApiResponse<AddressArea>>(
          `areas/${postalCode}`,
        );
        return response.data.data;
      } catch (err) {
        const { response } = err as AxiosError;

        if (response?.status !== HTTP_NOT_FOUND || !countryCode) {
          return {};
        }

        const newPostalCode =
          countryCode === "SE"
            ? `${postalCode.slice(0, 3)} ${postalCode.slice(3)}`
            : postalCode;
        const conditions = [
          `country_code="${countryCode}"`,
          `postal_code="${newPostalCode}"`,
        ];

        try {
          const url =
            "https://data.opendatasoft.com/api/explore/v2.1/catalog/datasets" +
            `/geonames-postal-code@public/records?select=place_name&where=${conditions.join(
              " and ",
            )}`;
          const response = await axios.get<GeonamesPostalCodeApiResponse>(url);

          if (response.data.total_count === 0) {
            return {};
          }

          return {
            area: response.data.results[0].place_name,
            postalCode,
          };
        } catch (err) {
          return {};
        }
      }
    },
    enabled: !!postalCode,
    keepPreviousData: true,
  });
};
