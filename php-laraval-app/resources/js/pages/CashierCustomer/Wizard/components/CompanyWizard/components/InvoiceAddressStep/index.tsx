/* eslint-disable @typescript-eslint/ban-ts-comment */
// @ts-nocheck
// Disable checks for this file because the file is not used right now
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
  InvoiceAddressFormValues,
  StepsValues,
} from "@/pages/Company/Wizard/types";

import { useGetCityByCountryService } from "@/services/city";
import { useGetGeocodeService } from "@/services/geocode";

import { PageProps } from "@/types";

const InvoiceAddressStep = () => {
  const { t } = useTranslation();

  const { countries, errors: serverErrors } =
    usePage<PageProps<CompanyWizardPageProps>>().props;

  const {
    stepsValues,
    isValidating,
    moveTo,
    onValidateSuccess,
    onValidateError,
  } = useWizard<StepsValues, InvoiceAddressFormValues>();

  const [countryLabel, setCountryLabel] = useState(DEFAULT_COUNTRY_NAME);
  const [cityLabel, setCityLabel] = useState("");

  const {
    register,
    watch,
    setValue,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<InvoiceAddressFormValues>({
    defaultValues: {
      ...stepsValues[3],
      invoiceCountry: stepsValues[3].invoiceCountry ?? DEFAULT_COUNTRY_ID,
    },
  });

  const countryId = watch("invoiceCountry");
  const postalCode = watch("invoicePostalCode");
  const address = watch("invoiceAddress");
  const latitude = watch("invoiceLatitude", 0);
  const longitude = watch("invoiceLongitude", 0);

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
      setValue("invoiceLatitude", geocode.data.latitude);
      setValue("invoiceLongitude", geocode.data.longitude);
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
        errorText={
          errors.invoiceCountry?.message || serverErrors.invoiceCountry
        }
        value={watch("invoiceCountry")}
        {...register("invoiceCountry", {
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
        errorText={
          errors.invoiceAddress?.message || serverErrors.invoiceAddress
        }
        {...register("invoiceAddress", {
          required: t("validation field required"),
        })}
        isRequired
      />
      <Flex gap={4}>
        <Input
          type="number"
          min={1}
          labelText={t("postal code")}
          errorText={
            errors.invoicePostalCode?.message || serverErrors.invoicePostalCode
          }
          {...register("invoicePostalCode", {
            required: t("validation field required"),
            minLength: {
              value: 1,
              message: t("validation field min", { min: 1 }),
            },
          })}
          isRequired
        />
        <Autocomplete
          options={cityOptions}
          labelText={t("postal locality")}
          errorText={
            errors.invoiceCityId?.message || serverErrors.invoiceCityId
          }
          value={watch("invoiceCityId")}
          {...register("invoiceCityId", {
            required: t("validation field required"),
            valueAsNumber: true,
            onChange: (e) => {
              const element = e.target as HTMLInputElement;
              const label = element.getAttribute("data-label");
              setCityLabel(label ?? "");
            },
          })}
          isLoading={cities.isLoading}
          isRequired
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
            setValue("invoiceLatitude", position.lat);
            setValue("invoiceLongitude", position.lng);
          }}
          mt={4}
          rounded="md"
        />
      </Skeleton>
      <Button type="submit" opacity={0} visibility="hidden" />
    </Flex>
  );
};

export default InvoiceAddressStep;
