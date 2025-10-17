import { UseQueryOptions, useQuery } from "@tanstack/react-query";

import { AddressGeocode } from "@/types/address";
import { Response } from "@/types/api";

interface GeocodeServiceProps {
  address: string;
  city: string;
  postalCode: string;
  country: string;
  options?: UseQueryOptions<Response<AddressGeocode>, unknown, AddressGeocode>;
}

export const useGetGeocodeService = ({
  address,
  city,
  postalCode,
  country,
  options,
}: GeocodeServiceProps) => {
  const addressQuery = `${address}, ${city}, ${postalCode}, ${country}`;

  return useQuery({
    queryKey: ["api", "v0", "geocode", `?address=${addressQuery}`],
    enabled: !!address && !!city && !!postalCode && !!country,
    select: (response) => response.data.data,
    ...options,
  });
};
