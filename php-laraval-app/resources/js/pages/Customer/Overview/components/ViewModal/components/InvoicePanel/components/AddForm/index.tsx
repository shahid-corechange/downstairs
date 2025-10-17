import {
  Button,
  Flex,
  Skeleton,
  Tab,
  TabList,
  TabPanel,
  TabPanels,
  Tabs,
  useConst,
} from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { router, usePage } from "@inertiajs/react";
import { useCallback, useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import Map from "@/components/Map";
import PhoneInput from "@/components/PhoneInput";

import { INVOICE_DUE_DAYS, INVOICE_METHODS } from "@/constants/invoice";
import { DEFAULT_COUNTRY_ID, DEFAULT_COUNTRY_NAME } from "@/constants/location";

import { useDebounce } from "@/hooks/debounce";

import { ViewModalPageProps } from "@/pages/Customer/Overview/types";

import { useGetCityByCountryService } from "@/services/city";
import { useGetCountries } from "@/services/country";
import { useGetCustomersAddresses } from "@/services/customer";
import { useGetGeocodeService } from "@/services/geocode";

import Customer from "@/types/customer";

import { getTranslatedOptions } from "@/utils/autocomplete";
import { validateEmail, validatePhone } from "@/utils/validation";

import { PageProps } from "@/types";

type FormValues = {
  name: string;
  membershipType: "private" | "company";
  reference: string;
  identityNumber: string;
  email: string;
  phone1: string;
  dueDays: number;
  invoiceMethod: "email" | "print";
  country: number;
  cityId: number;
  postalCode: string;
  address: string;
  address2: string;
};

const panelFields: (keyof FormValues)[][] = [
  [
    "name",
    "reference",
    "identityNumber",
    "email",
    "dueDays",
    "invoiceMethod",
    "phone1",
  ],
  ["country", "address", "address2", "postalCode", "cityId"],
];

interface AddFormProps {
  userId: number;
  onCancel: () => void;
  onRefetch: () => void;
  primaryAddress?: Customer;
}

const AddForm = ({
  userId,
  primaryAddress,
  onCancel,
  onRefetch,
}: AddFormProps) => {
  const { t } = useTranslation();
  const { dueDays, errors: serverErrors } =
    usePage<PageProps<ViewModalPageProps>>().props;
  const {
    register,
    watch,
    reset,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    defaultValues: {
      country: DEFAULT_COUNTRY_ID,
      dueDays,
      membershipType: "private",
      invoiceMethod: "print",
    },
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [countryLabel, setCountryLabel] = useState(DEFAULT_COUNTRY_NAME);
  const [cityLabel, setCityLabel] = useState("");
  const [latitude, setLatitude] = useState(0);
  const [longitude, setLongitude] = useState(0);
  const [customerRefId, setCustomerRefId] = useState<number>();
  const [tabIndex, setTabIndex] = useState(0);
  const [prefill, setPrefill] = useState<string>();

  const countryId = watch("country");
  const postalCode = watch("postalCode");
  const address = watch("address");

  const prefillOptions = useConst([
    {
      label: t("primary address"),
      value: "primary",
    },
    {
      label: t("existing address"),
      value: "existing",
    },
  ]);

  const allAddresses = useGetCustomersAddresses({
    request: {
      size: -1,
      filter: {
        eq: {
          customerRefId: "null",
        },
      },
      include: ["address.city.country"],
      only: [
        "id",
        "name",
        "reference",
        "identityNumber",
        "email",
        "formattedPhone1",
        "address.address",
        "address.address2",
        "address.postalCode",
        "address.latitude",
        "address.longitude",
        "address.cityId",
        "address.city.name",
        "address.city.countryId",
        "address.city.country.name",
      ],
    },
  });

  const countries = useGetCountries({
    request: {
      only: ["id", "name", "code", "dialCode"],
    },
  });

  const dialCodes = useMemo(
    () => countries.data?.map((country) => `+${country.dialCode}`) ?? [],
    [countries.data],
  );

  const debouncedAddress = useDebounce(
    { address, city: cityLabel, postalCode, country: countryLabel },
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
      enabled:
        !prefill && !!address && !!cityLabel && !!postalCode && !!countryLabel,
    },
  });

  const allAddressesOptions = useMemo(
    () =>
      (allAddresses.data ?? []).map((item) => ({
        label: `${item.name} (${item.email})`,
        value: item.id,
      })),
    [allAddresses.data],
  );

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

  const invoiceMethodOptions = getTranslatedOptions(INVOICE_METHODS);

  const handleChangePrefillOption = useCallback(
    (value: string) => {
      setPrefill(value);

      if (value === "primary" && primaryAddress) {
        const updates = {
          name: primaryAddress.name,
          reference: primaryAddress.reference ?? "",
          identityNumber: primaryAddress.identityNumber,
          email: primaryAddress.email,
          phone1: primaryAddress.formattedPhone1,
          country: primaryAddress.address?.city?.countryId,
          cityId: primaryAddress.address?.cityId,
          address: primaryAddress.address?.address,
          address2: primaryAddress.address?.address2 ?? "",
          postalCode: primaryAddress.address?.postalCode,
        } as const;

        const validUpdates = Object.fromEntries(
          // eslint-disable-next-line @typescript-eslint/no-unused-vars
          Object.entries(updates).filter(([_, value]) => value !== undefined),
        );

        reset({
          ...watch(),
          ...validUpdates,
        });

        setCountryLabel(
          primaryAddress?.address?.city?.country?.name ?? DEFAULT_COUNTRY_NAME,
        );
        setCityLabel(primaryAddress?.address?.city?.name ?? "");
        setLatitude(primaryAddress?.address?.latitude ?? 0);
        setLongitude(primaryAddress?.address?.longitude ?? 0);
        setCustomerRefId(primaryAddress.id);
      } else {
        reset(undefined, { keepDefaultValues: true, keepValues: false });
        setCountryLabel(DEFAULT_COUNTRY_NAME);
        setCityLabel("");
        setLatitude(0);
        setLongitude(0);
        setCustomerRefId(undefined);
      }
    },
    [primaryAddress],
  );

  const handleSelectExisting = useCallback(
    (addressId: number) => {
      const address = (allAddresses.data ?? []).find(
        (item) => item.id === addressId,
      );

      if (!address) {
        return;
      }

      const updates = {
        name: address.name,
        reference: address.reference ?? "",
        identityNumber: address.identityNumber,
        email: address.email,
        phone1: address.formattedPhone1,
        country: address.address?.city?.countryId,
        cityId: address.address?.cityId,
        address: address.address?.address,
        address2: address.address?.address2 ?? "",
        postalCode: address.address?.postalCode,
      } as const;

      const validUpdates = Object.fromEntries(
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        Object.entries(updates).filter(([_, value]) => value !== undefined),
      );

      reset({
        ...watch(),
        ...validUpdates,
      });

      setCountryLabel(
        address?.address?.city?.country?.name ?? DEFAULT_COUNTRY_NAME,
      );
      setCityLabel(address?.address?.city?.name ?? "");
      setLatitude(address?.address?.latitude ?? 0);
      setLongitude(address?.address?.longitude ?? 0);
      setCustomerRefId(address.id);
    },
    [allAddresses.data, primaryAddress],
  );

  const handleError = (errors: (keyof FormValues)[]) => {
    const field = errors[0];
    const index = panelFields.findIndex((fields) => fields.includes(field));
    setTabIndex(index);
  };

  const handleSubmit = formSubmitHandler(
    (values) => {
      setIsSubmitting(true);
      router.post(
        `/customers/${userId}/addresses`,
        customerRefId
          ? {
              customerRefId,
              membershipType: values.membershipType,
              email: values.email,
              dueDays: values.dueDays,
              invoiceMethod: values.invoiceMethod,
              reference: values.reference,
            }
          : {
              latitude,
              longitude,
              ...values,
            },
        {
          onFinish: () => setIsSubmitting(false),
          onSuccess: (page) => {
            const {
              flash: { error },
            } = (page as Page<PageProps>).props;

            if (error) {
              return;
            }

            onCancel();
            onRefetch();
          },
          onError: (errors) => {
            handleError(Object.keys(errors) as (keyof FormValues)[]);
          },
        },
      );
    },
    (errors) => {
      handleError(Object.keys(errors) as (keyof FormValues)[]);
    },
  );

  useEffect(() => {
    if (geocode.data) {
      setLatitude(geocode.data.latitude);
      setLongitude(geocode.data.longitude);
    }
  }, [geocode.data]);

  return (
    <Flex
      as="form"
      direction="column"
      gap={4}
      onSubmit={handleSubmit}
      autoComplete="off"
      noValidate
    >
      <Autocomplete
        options={prefillOptions}
        labelText={t("prefill")}
        helperText={t("prefill helper text")}
        onChange={(e) => handleChangePrefillOption(e.target.value)}
        allowEmpty
      />
      {prefill === "existing" && (
        <Autocomplete
          options={allAddressesOptions}
          labelText={t("existing addresses")}
          helperText={t("select an existing address to prefill")}
          onChange={(e) => handleSelectExisting(Number(e.target.value))}
        />
      )}
      <Tabs index={tabIndex} onChange={setTabIndex}>
        <TabList>
          <Tab>{t("profile")}</Tab>
          <Tab>{t("address")}</Tab>
        </TabList>
        <TabPanels>
          <TabPanel display="flex" flexDirection="column" gap={4} py={6}>
            {customerRefId ? (
              <Input
                labelText={t("name")}
                defaultValue={watch("name")}
                isRequired
                isReadOnly
              />
            ) : (
              <Input
                labelText={t("name")}
                errorText={errors.name?.message || serverErrors.name}
                {...register("name", {
                  required: t("validation field required"),
                })}
                isRequired
              />
            )}
            <Input
              labelText={t("customer reference")}
              errorText={errors.reference?.message || serverErrors.reference}
              {...register("reference")}
            />
            {customerRefId ? (
              <Input
                labelText={t("identity number")}
                defaultValue={watch("identityNumber")}
                isRequired
                isReadOnly
              />
            ) : (
              <Input
                labelText={t("identity number")}
                errorText={
                  errors.identityNumber?.message || serverErrors.identityNumber
                }
                {...register("identityNumber", {
                  required: t("validation field required"),
                })}
                isRequired
              />
            )}
            {prefill === "existing" && customerRefId ? (
              <Input
                labelText={t("email")}
                defaultValue={watch("email")}
                isRequired
                isReadOnly
              />
            ) : (
              <Input
                labelText={t("email")}
                errorText={errors.email?.message || serverErrors.email}
                {...register("email", {
                  required: t("validation field required"),
                  validate: { email: validateEmail },
                })}
                isRequired
              />
            )}
            <Flex gap={4}>
              <Autocomplete
                options={INVOICE_DUE_DAYS}
                labelText={t("invoice due days")}
                errorText={errors.dueDays?.message || serverErrors.dueDays}
                value={watch("dueDays")}
                {...register("dueDays", {
                  required: t("validation field required"),
                  valueAsNumber: true,
                })}
                isRequired
              />
              <Autocomplete
                options={invoiceMethodOptions}
                labelText={t("send invoice method")}
                errorText={
                  errors.invoiceMethod?.message || serverErrors.invoiceMethod
                }
                value={watch("invoiceMethod")}
                {...register("invoiceMethod", {
                  required: t("validation field required"),
                })}
                isRequired
              />
            </Flex>
            {customerRefId ? (
              <Input
                labelText={t("phone")}
                defaultValue={watch("phone1")}
                isRequired
                isReadOnly
              />
            ) : (
              <PhoneInput
                labelText={t("phone")}
                dialCodes={dialCodes}
                errorText={errors.phone1?.message || serverErrors.phone1}
                {...register("phone1", {
                  required: t("validation field required"),
                  validate: validatePhone,
                })}
                isRequired
              />
            )}
          </TabPanel>
          <TabPanel display="flex" flexDirection="column" gap={4} py={6}>
            {customerRefId ? (
              <Input
                labelText={t("country")}
                defaultValue={countryLabel}
                isRequired
                isReadOnly
              />
            ) : (
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
                isLoading={countries.isLoading}
                isRequired
              />
            )}
            {customerRefId ? (
              <Input
                labelText={t("address")}
                defaultValue={address}
                isRequired
                isReadOnly
              />
            ) : (
              <Input
                labelText={t("address")}
                errorText={errors.address?.message || serverErrors.address}
                {...register("address", {
                  required: t("validation field required"),
                })}
                isRequired
              />
            )}
            {customerRefId ? (
              <Input
                labelText={t("address 2")}
                defaultValue={watch("address2")}
                isReadOnly
              />
            ) : (
              <Input
                labelText={t("address 2")}
                errorText={errors.address2?.message || serverErrors.address2}
                {...register("address2")}
              />
            )}
            <Flex gap={4}>
              {customerRefId ? (
                <Input
                  labelText={t("postal code")}
                  defaultValue={postalCode}
                  isRequired
                  isReadOnly
                />
              ) : (
                <Input
                  type="number"
                  labelText={t("postal code")}
                  errorText={
                    errors.postalCode?.message || serverErrors.postalCode
                  }
                  {...register("postalCode", {
                    required: t("validation field required"),
                    minLength: {
                      value: 1,
                      message: t("validation field min", { min: 1 }),
                    },
                  })}
                  isRequired
                />
              )}
              {customerRefId ? (
                <Input
                  labelText={t("postal locality")}
                  defaultValue={cityLabel}
                  isRequired
                  isReadOnly
                />
              ) : (
                <Autocomplete
                  options={cityOptions}
                  labelText={t("postal locality")}
                  errorText={errors.cityId?.message || serverErrors.cityId}
                  value={watch("cityId")}
                  {...register("cityId", {
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
              )}
            </Flex>
            <Skeleton isLoaded={!geocode.isFetching} rounded="md">
              <Map
                h="200px"
                mt={4}
                rounded="md"
                center={{
                  lat: latitude,
                  lng: longitude,
                }}
                markers={[
                  {
                    draggable: !customerRefId,
                    position: {
                      lat: latitude,
                      lng: longitude,
                    },
                  },
                ]}
                onMarkerMove={(position) => {
                  setLatitude(position.lat);
                  setLongitude(position.lng);
                }}
              />
            </Skeleton>
          </TabPanel>
        </TabPanels>
      </Tabs>
      <Flex justify="right" mt={4} gap={4}>
        <Button colorScheme="gray" fontSize="sm" onClick={onCancel}>
          {t("close")}
        </Button>
        <Button
          type="submit"
          fontSize="sm"
          isLoading={isSubmitting}
          loadingText={t("please wait")}
        >
          {t("save")}
        </Button>
      </Flex>
    </Flex>
  );
};

export default AddForm;
