import { Flex, Skeleton, TabPanel, TabPanelProps } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useEffect, useMemo, useRef, useState } from "react";
import {
  FieldErrors,
  UseFormRegister,
  UseFormSetValue,
  UseFormWatch,
} from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import Map from "@/components/Map";

import { DEFAULT_CITY_NAME, DEFAULT_COUNTRY_NAME } from "@/constants/location";

import { useDebounce } from "@/hooks/debounce";

import { useGetCityByCountryService } from "@/services/city";
import { useGetCountries } from "@/services/country";
import { useGetGeocodeService } from "@/services/geocode";

import Property from "@/types/property";

import { FormValues } from "../../types";

interface AddressPanelProps extends TabPanelProps {
  register: UseFormRegister<FormValues>;
  errors: FieldErrors<FormValues>;
  watch: UseFormWatch<FormValues>;
  setValue: UseFormSetValue<FormValues>;
  data?: Property;
}

const AddressPanel = ({
  register,
  errors,
  watch,
  setValue,
  data,
  ...props
}: AddressPanelProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;
  const countries = useGetCountries({
    request: {
      only: ["id", "name", "code", "dialCode"],
    },
  });

  const [countryLabel, setCountryLabel] = useState(
    data?.address?.city?.country?.name ?? DEFAULT_COUNTRY_NAME,
  );
  const [cityLabel, setCityLabel] = useState(
    data?.address?.city?.name ?? DEFAULT_CITY_NAME,
  );

  const isLocationUpdated = useRef(false);

  const address = watch("address");
  const postalCode = watch("postalCode");
  const countryId = watch("countryId");
  const cityId = watch("cityId");
  const latitude = watch("latitude");
  const longitude = watch("longitude");

  const debouncedAddress = useDebounce(
    {
      address,
      city: cityLabel,
      postalCode,
      country: countryLabel,
    },
    1000,
  );

  const cities = useGetCityByCountryService(countryId, {
    request: {
      only: ["id", "name"],
    },
  });

  const geocode = useGetGeocodeService({
    ...debouncedAddress,
    options: {
      enabled: isLocationUpdated.current,
    },
  });

  const countryOptions = useMemo(
    () =>
      countries.data?.map((item) => ({
        label: item.name,
        value: item.id,
      })) ?? [],
    [countries.data],
  );

  const cityOptions = useMemo(
    () =>
      cities.data?.map((item) => ({ label: item.name, value: item.id })) ?? [],
    [cities.data],
  );

  useEffect(() => {
    if (geocode.data) {
      setValue("latitude", geocode.data.latitude);
      setValue("longitude", geocode.data.longitude);
    }
  }, [geocode.data]);

  return (
    <TabPanel {...props}>
      <Autocomplete
        options={countryOptions}
        labelText={t("country")}
        errorText={errors.countryId?.message}
        value={countryId}
        {...register("countryId", {
          required: t("validation field required"),
          valueAsNumber: true,
          onChange: (e) => {
            const element = e.target as HTMLInputElement;
            const label = element.getAttribute("data-label");
            setCountryLabel(label ?? "");
            isLocationUpdated.current = true;
          },
        })}
        isLoading={countries.isLoading}
        isRequired
      />
      <Input
        labelText={t("address")}
        errorText={errors.address?.message || serverErrors.address}
        {...register("address", {
          required: t("validation field required"),
          onChange: () => {
            isLocationUpdated.current = true;
          },
        })}
        isRequired
      />
      <Flex gap={4}>
        <Input
          type="number"
          labelText={t("postal code")}
          errorText={errors.postalCode?.message || serverErrors.postalCode}
          {...register("postalCode", {
            required: t("validation field required"),
            minLength: {
              value: 1,
              message: t("validation field min", { min: 1 }),
            },
            onChange: () => {
              isLocationUpdated.current = true;
            },
          })}
          isRequired
        />
        <Autocomplete
          options={cityOptions}
          labelText={t("postal locality")}
          errorText={errors.cityId?.message || serverErrors.cityId}
          value={cityId}
          {...register("cityId", {
            required: t("validation field required"),
            valueAsNumber: true,
            onChange: (e) => {
              const element = e.target as HTMLInputElement;
              const label = element.getAttribute("data-label");
              setCityLabel(label ?? "");
              isLocationUpdated.current = true;
            },
          })}
          isLoading={cities.isLoading}
          isRequired
        />
      </Flex>
      <Skeleton isLoaded={!geocode.isFetching} rounded="md">
        <Map
          h="300px"
          mt={4}
          rounded="md"
          center={{
            lat: latitude,
            lng: longitude,
          }}
          markers={[
            {
              draggable: true,
              position: {
                lat: latitude,
                lng: longitude,
              },
            },
          ]}
          onMarkerMove={(position) => {
            setValue("latitude", position.lat);
            setValue("longitude", position.lng);
          }}
        />
      </Skeleton>
    </TabPanel>
  );
};

export default AddressPanel;
