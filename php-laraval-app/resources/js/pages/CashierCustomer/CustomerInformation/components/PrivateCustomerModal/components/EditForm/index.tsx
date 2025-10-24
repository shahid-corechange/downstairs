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
import { router, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useRef, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import Map from "@/components/Map";
import PhoneInput from "@/components/PhoneInput";

import { INVOICE_DUE_DAYS, INVOICE_METHODS } from "@/constants/invoice";
import { DEFAULT_COUNTRY_ID, DEFAULT_COUNTRY_NAME } from "@/constants/location";
import { NOTIFICATION_METHOD_OPTIONS } from "@/constants/notification";

import locales from "@/data/locales.json";

import { useDebounce } from "@/hooks/debounce";

import { useGetCityByCountryService } from "@/services/city";
import { useGetCountries } from "@/services/country";
import { useGetGeocodeService } from "@/services/geocode";

import Customer from "@/types/customer";
import CustomerDiscount from "@/types/customerDiscount";

import { getTranslatedOptions } from "@/utils/autocomplete";
import { validateEmail, validatePhone } from "@/utils/validation";

type FormValues = {
  customerId: number;
  name: string;
  identityNumber: string;
  address: string;
  email: string;
  phone1: string;
  dueDays: number;
  invoiceMethod: string;
  country: number;
  cityId: number;
  postalCode: string;
  area: string;
  address2?: string;
  discountId?: number;
  discountPercentage?: number;
  // user
  firstName: string;
  lastName: string;
  userEmail: string;
  cellphone: string;
  language: string;
  timezone: string;
  notificationMethod: string;
  status: string;
};

const panelFields: (keyof FormValues)[][] = [
  ["name", "identityNumber", "email", "dueDays", "invoiceMethod", "phone1"],
  ["country", "address", "address2", "postalCode", "cityId"],
  [
    "firstName",
    "lastName",
    "userEmail",
    "cellphone",
    "language",
    "timezone",
    "notificationMethod",
    "status",
  ],
];

interface EditFormProps {
  userId: number;
  customer: Customer;
  onCancel: () => void;
  onRefetch: () => void;
  discount?: CustomerDiscount;
}

const EditForm = ({
  userId,
  customer,
  discount,
  onCancel,
  onRefetch,
}: EditFormProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;
  const {
    register,
    watch,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    defaultValues: {
      customerId: customer.id,
      name: customer.name,
      identityNumber: customer.identityNumber,
      address: customer.address?.address,
      address2: customer.address?.address2,
      email: customer.email,
      phone1: customer.formattedPhone1,
      dueDays: customer.dueDays,
      invoiceMethod: customer.invoiceMethod,
      country: customer.address?.city?.countryId ?? DEFAULT_COUNTRY_ID,
      cityId: customer.address?.cityId,
      postalCode: customer.address?.postalCode,
      discountId: discount?.id,
      discountPercentage: discount?.value,
      // User
      firstName: customer.users?.[0]?.firstName,
      lastName: customer.users?.[0]?.lastName,
      userEmail: customer.users?.[0]?.email,
      cellphone: customer.users?.[0]?.formattedCellphone,
      language: customer.users?.[0]?.info?.language ?? "",
      timezone: customer.users?.[0]?.info?.timezone ?? "",
      notificationMethod: customer.users?.[0]?.info?.notificationMethod ?? "",
      status: customer.users?.[0]?.status,
    },
  });

  const [isSubmitting, setIsSubmitting] = useState(false);
  const [countryLabel, setCountryLabel] = useState(
    customer.address?.city?.country?.name ?? DEFAULT_COUNTRY_NAME,
  );
  const [cityLabel, setCityLabel] = useState(
    customer.address?.city?.name ?? "",
  );
  const [latitude, setLatitude] = useState(customer.address?.latitude ?? 0);
  const [longitude, setLongitude] = useState(customer.address?.longitude ?? 0);
  const [tabIndex, setTabIndex] = useState(0);

  const isLocationUpdated = useRef(false);

  const countryId = watch("country");
  const address = watch("address");
  const postalCode = watch("postalCode");
  const cityId = watch("cityId");
  const userEmail = watch("userEmail");

  const countries = useGetCountries({
    request: {
      only: ["id", "name", "code", "dialCode"],
    },
  });

  const dialCodes = useMemo(
    () => countries.data?.map((country) => `+${country.dialCode}`) ?? [],
    [countries.data],
  );

  // exclude email notification method if email is not provided
  const notificationMethodOptions = useMemo(
    () =>
      getTranslatedOptions(NOTIFICATION_METHOD_OPTIONS).filter((option) =>
        !userEmail ? option.value !== "email" : true,
      ),
    [userEmail],
  );

  const statusOptions = useConst(
    getTranslatedOptions([
      "active",
      "inactive",
      "suspended",
      "pending",
      "blocked",
    ]),
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

  const invoiceMethodOptions = getTranslatedOptions(INVOICE_METHODS);

  // to update address, needs to provide address, postal code, and city id
  const isAddressRequired = useMemo(
    () => !!(address || postalCode || cityId),
    [address, postalCode, cityId],
  );

  const handleError = (errors: (keyof FormValues)[]) => {
    const field = errors[0];
    const index = panelFields.findIndex((fields) => fields.includes(field));
    setTabIndex(index);
  };

  const handleSubmit = formSubmitHandler(
    (values) => {
      setIsSubmitting(true);

      const payload = {
        ...values,
        membershipType: "private",
        latitude: latitude,
        longitude: longitude,
      };

      // Submit Customer First
      router.patch(`/cashier/customers/${userId}/private`, payload, {
        onFinish: () => setIsSubmitting(false),
        onSuccess: () => {
          onCancel();
          onRefetch();
        },
        onError: (errors) => {
          handleError(Object.keys(errors) as (keyof FormValues)[]);
        },
      });
    },
    (errors) => {
      setIsSubmitting(false);
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
      onSubmit={handleSubmit}
      autoComplete="off"
      noValidate
    >
      <Tabs index={tabIndex} onChange={setTabIndex}>
        <TabList>
          <Tab>{t("profile")}</Tab>
          <Tab>{t("address")}</Tab>
          <Tab>{t("user info")}</Tab>
        </TabList>
        <TabPanels>
          <TabPanel display="flex" flexDirection="column" gap={4} py={6}>
            <Input
              labelText={t("name")}
              errorText={errors.name?.message || serverErrors.name}
              isRequired
              {...register("name", {
                required: t("validation field required"),
              })}
            />
            <Input
              labelText={t("identity number")}
              errorText={
                errors.identityNumber?.message || serverErrors.identityNumber
              }
              {...register("identityNumber", {
                required: customer.isFull
                  ? t("validation field required")
                  : false,
              })}
              isRequired={customer.isFull}
            />
            <Input
              labelText={t("email")}
              errorText={errors.email?.message || serverErrors.email}
              {...register("email", {
                required: customer.isFull
                  ? t("validation field required")
                  : false,
                validate: {
                  email: (value) => {
                    if (!value) {
                      return true;
                    }
                    return validateEmail(value);
                  },
                },
              })}
              isRequired={customer.isFull}
            />
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
            <PhoneInput
              labelText={t("phone")}
              errorText={errors.phone1?.message || serverErrors.phone1}
              dialCodes={dialCodes}
              value={watch("phone1")}
              isRequired
              {...register("phone1", {
                required: t("validation field required"),
                validate: validatePhone,
              })}
            />
            <Input
              labelText={t("discount percentage")}
              errorText={
                errors.discountPercentage?.message ||
                serverErrors.discountPercentage
              }
              type="number"
              {...register("discountPercentage", {
                min: {
                  value: 0,
                  message: t("validation field min", { min: 0 }),
                },
                max: {
                  value: 100,
                  message: t("validation field max", { max: 100 }),
                },
                setValueAs: (value) => {
                  const num = Number(value);
                  return isNaN(num) ? 0 : num;
                },
              })}
              suffix="%"
            />
          </TabPanel>
          <TabPanel display="flex" flexDirection="column" gap={4} py={6}>
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
                required:
                  isAddressRequired || customer.isFull
                    ? t("validation field required")
                    : false,
                onChange: () => {
                  isLocationUpdated.current = true;
                },
              })}
              isRequired={isAddressRequired || customer.isFull}
            />
            <Input
              labelText={t("address 2")}
              errorText={errors.address2?.message || serverErrors.address2}
              {...register("address2")}
            />
            <Flex gap={4}>
              <Input
                type="number"
                labelText={t("postal code")}
                errorText={
                  errors.postalCode?.message || serverErrors.postalCode
                }
                {...register("postalCode", {
                  required:
                    isAddressRequired || customer.isFull
                      ? t("validation field required")
                      : false,
                  minLength: {
                    value: 1,
                    message: t("validation field min", { min: 1 }),
                  },
                  onChange: () => {
                    isLocationUpdated.current = true;
                  },
                })}
                isRequired={isAddressRequired || customer.isFull}
              />
              <Autocomplete
                options={cityOptions}
                labelText={t("postal locality")}
                errorText={errors.cityId?.message || serverErrors.cityId}
                value={cityId}
                {...register("cityId", {
                  required:
                    isAddressRequired || customer.isFull
                      ? t("validation field required")
                      : false,
                  valueAsNumber: true,
                  onChange: (e) => {
                    const element = e.target as HTMLInputElement;
                    const label = element.getAttribute("data-label");
                    setCityLabel(label ?? "");
                    isLocationUpdated.current = true;
                  },
                })}
                isLoading={cities.isLoading}
                isRequired={isAddressRequired || customer.isFull}
              />
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
                    draggable: true,
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
          <TabPanel display="flex" flexDirection="column" gap={4} py={6}>
            <Flex gap={4}>
              <Input
                labelText={t("first name")}
                errorText={errors.firstName?.message || serverErrors.firstName}
                isRequired
                {...register("firstName", {
                  required: t("validation field required"),
                })}
              />
              <Input
                labelText={t("last name")}
                errorText={errors.lastName?.message || serverErrors.lastName}
                {...register("lastName", {
                  required:
                    customer.users?.[0]?.email || userEmail
                      ? t("validation field required")
                      : false,
                })}
                isRequired={!!(customer.users?.[0]?.email || userEmail)}
              />
            </Flex>
            <Input
              labelText={t("user email")}
              errorText={errors.userEmail?.message || serverErrors.userEmail}
              {...register("userEmail", {
                required:
                  customer.users?.[0]?.email || userEmail
                    ? t("validation field required")
                    : false,
                validate: {
                  email: (value) => {
                    if (!value) {
                      return true;
                    }
                    return validateEmail(value);
                  },
                },
              })}
              type="email"
              isRequired={!!(customer.users?.[0]?.email || userEmail)}
            />
            <PhoneInput
              labelText={t("user phone")}
              errorText={errors.cellphone?.message || serverErrors.cellphone}
              dialCodes={dialCodes}
              value={watch("cellphone")}
              {...register("cellphone", {
                required: t("validation field required"),
                validate: validatePhone,
              })}
              isRequired
            />
            <Flex gap={4}>
              <Input
                labelText={t("timezone")}
                defaultValue={watch("timezone")}
                isRequired
                isDisabled
              />
              <Autocomplete
                options={locales}
                labelText={t("language")}
                errorText={errors.language?.message || serverErrors.language}
                value={watch("language")}
                isRequired
                {...register("language", {
                  required: t("validation field required"),
                })}
              />
            </Flex>
            <Autocomplete
              options={notificationMethodOptions}
              labelText={t("notification method")}
              errorText={
                errors.notificationMethod?.message ||
                serverErrors.notificationMethod
              }
              value={watch("notificationMethod")}
              {...register("notificationMethod", {
                required: t("validation field required"),
              })}
              isRequired
            />
            <Autocomplete
              options={statusOptions}
              labelText={t("status")}
              errorText={errors.status?.message || serverErrors.status}
              value={watch("status")}
              {...register("status", {
                required: t("validation field required"),
              })}
              isRequired
            />
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

export default EditForm;
