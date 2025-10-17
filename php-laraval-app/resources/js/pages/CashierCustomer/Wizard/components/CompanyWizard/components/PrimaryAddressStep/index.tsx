import { Button, Flex, Skeleton } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import Map from "@/components/Map";
import { useWizard } from "@/components/Wizard/hooks";

import { DEFAULT_COUNTRY_ID, DEFAULT_COUNTRY_NAME } from "@/constants/location";

import { useDebounce } from "@/hooks/debounce";

import {
  CompanyWizardPageProps,
  PrimaryAddressFormValues,
  StepsValues,
} from "@/pages/Company/Wizard/types";

import { useGetCityByCountryService } from "@/services/city";
import { useGetGeocodeService } from "@/services/geocode";

import { PageProps } from "@/types";

const PrimaryAddressStep = () => {
  const { t } = useTranslation();

  const { countries, errors: serverErrors } =
    usePage<PageProps<CompanyWizardPageProps>>().props;

  const {
    stepsValues,
    isValidating,
    moveTo,
    onValidateSuccess,
    onValidateError,
  } = useWizard<StepsValues, PrimaryAddressFormValues>();

  const [countryLabel, setCountryLabel] = useState(DEFAULT_COUNTRY_NAME);
  const [cityLabel, setCityLabel] = useState("");

  const {
    register,
    watch,
    setValue,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<PrimaryAddressFormValues>({
    defaultValues: {
      ...stepsValues[2],
      country: stepsValues[2].country ?? DEFAULT_COUNTRY_ID,
    },
  });

  const countryId = watch("country");
  const postalCode = watch("postalCode");
  const address = watch("address");
  const latitude = watch("latitude", 0);
  const longitude = watch("longitude", 0);
  const companyEmail = stepsValues[0].companyEmail;

  const debouncedAddress = useDebounce(
    { address, city: cityLabel, postalCode, country: countryLabel },
    1000,
  );

  const cities = useGetCityByCountryService(countryId);
  const geocode = useGetGeocodeService({
    ...debouncedAddress,
  });

  const countryOptions = useMemo(
    () =>
      countries.map((item) => ({
        label: item.name,
        value: item.id,
      })),
    [countries],
  );

  const cityOptions = useMemo(
    () =>
      cities.data
        ? cities.data.map((item) => ({ label: item.name, value: item.id }))
        : [],
    [cities.data],
  );

  const handleSubmit = formSubmitHandler(onValidateSuccess, onValidateError);

  useEffect(() => {
    if (geocode.data) {
      setValue("latitude", geocode.data.latitude);
      setValue("longitude", geocode.data.longitude);
    }
  }, [geocode.data]);

  useEffect(() => {
    if (isValidating) {
      handleSubmit();
    }
  }, [isValidating]);

  return (
    <Flex
      as="form"
      w="full"
      direction="column"
      gap={4}
      onSubmit={(e) => {
        e.preventDefault();
        moveTo("next");
      }}
      autoComplete="off"
      noValidate
    >
      <Autocomplete
        options={countryOptions}
        labelText={t("country")}
        errorText={errors.country?.message || serverErrors.country}
        value={watch("country")}
        {...register("country", {
          required: t("validation field required"),
          valueAsNumber: true,
          onChange: (e) => {
            const element = e.target as HTMLInputElement;
            const label = element.getAttribute("data-label");
            setCountryLabel(label ?? "");
          },
        })}
        isRequired
      />
      <Input
        labelText={t("address")}
        errorText={errors.address?.message || serverErrors.address}
        {...register("address", {
          required: companyEmail ? t("validation field required") : false,
        })}
        isRequired={!!companyEmail}
      />
      <Input
        labelText={t("address 2")}
        errorText={errors.address2?.message || serverErrors.address2}
        {...register("address2")}
      />
      <Flex gap={4}>
        <Input
          type="number"
          min={1}
          labelText={t("postal code")}
          errorText={errors.postalCode?.message || serverErrors.postalCode}
          {...register("postalCode", {
            required: companyEmail ? t("validation field required") : false,
            minLength: {
              value: 1,
              message: t("validation field min", { min: 1 }),
            },
          })}
          isRequired={!!companyEmail}
        />
        <Autocomplete
          options={cityOptions}
          labelText={t("postal locality")}
          errorText={errors.cityId?.message || serverErrors.cityId}
          value={watch("cityId")}
          {...register("cityId", {
            required: companyEmail ? t("validation field required") : false,
            valueAsNumber: true,
            onChange: (e) => {
              const element = e.target as HTMLInputElement;
              const label = element.getAttribute("data-label");
              setCityLabel(label ?? "");
            },
          })}
          isLoading={cities.isLoading}
          isRequired={!!companyEmail}
        />
      </Flex>
      <Skeleton isLoaded={!geocode.isFetching} rounded="md">
        <Map
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
          mt={4}
          rounded="md"
        />
      </Skeleton>
      <Button type="submit" opacity={0} visibility="hidden" />
    </Flex>
  );
};

export default PrimaryAddressStep;
