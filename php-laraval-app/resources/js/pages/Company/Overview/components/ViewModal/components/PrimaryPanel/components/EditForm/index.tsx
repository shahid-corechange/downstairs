import {
  Button,
  Flex,
  Skeleton,
  Tab,
  TabList,
  TabPanel,
  TabPanels,
  Tabs,
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
import { DEFAULT_COUNTRY_NAME } from "@/constants/location";
import { NOTIFICATION_METHOD_OPTIONS } from "@/constants/notification";

import { useDebounce } from "@/hooks/debounce";

import { useGetCityByCountryService } from "@/services/city";
import { useGetCountries } from "@/services/country";
import { useGetGeocodeService } from "@/services/geocode";

import Customer from "@/types/customer";
import User, { UserInfo } from "@/types/user";

import { getTranslatedOptions } from "@/utils/autocomplete";
import { validateEmail, validatePhone } from "@/utils/validation";

type FormValues = {
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
  // Company info fields
  companyName: string;
  companyEmail: string;
  companyPhone: string;
  notificationMethod: string;
};

const panelFields: (keyof FormValues)[][] = [
  ["name", "identityNumber", "email", "dueDays", "invoiceMethod", "phone1"],
  ["country", "address", "address2", "postalCode", "cityId"],
  ["companyName", "companyEmail", "companyPhone", "notificationMethod"],
];

interface EditFormProps {
  companyId: number;
  userData: User;
  customer: Customer;
  onCancel: () => void;
  onRefetch: () => void;
  onUserDataUpdate: (updatedUser: User) => void;
  onTabChange?: (tabIndex: number) => void;
}

const EditForm = ({
  companyId,
  userData,
  customer,
  onCancel,
  onRefetch,
  onUserDataUpdate,
  onTabChange,
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
      name: customer.name,
      identityNumber: customer.identityNumber,
      address: customer.address?.address ?? "",
      address2: customer.address?.address2 ?? "",
      email: customer.email,
      dueDays: customer.dueDays,
      invoiceMethod: customer.invoiceMethod,
      phone1: customer.formattedPhone1,
      country: customer.address?.city?.countryId,
      cityId: customer.address?.cityId,
      postalCode: customer.address?.postalCode,
      // Company info defaults
      companyName: userData.fullname,
      companyEmail: userData.email,
      companyPhone: userData.formattedCellphone,
      notificationMethod: userData.info?.notificationMethod ?? "",
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
  const postalCode = watch("postalCode");
  const address = watch("address");
  const email = watch("email");

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

  // exclude app notification method
  // exclude email notification method if email is not provided
  const notificationMethodOptions = useMemo(
    () =>
      getTranslatedOptions(NOTIFICATION_METHOD_OPTIONS).filter(
        (option) =>
          option.value !== "app" && (!email ? option.value !== "email" : true),
      ),
    [email],
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
        membershipType: "company",
        latitude,
        longitude,
        // user info fields
        firstName: values.companyName,
        lastName: "",
        userEmail: values.companyEmail ?? null,
        cellphone: values.companyPhone,
        language: userData.info?.language ?? "",
        timezone: userData.info?.timezone ?? "",
        notificationMethod: values.notificationMethod,
      };

      // Update customer address first
      router.patch(
        `/companies/${companyId}/addresses/${customer.id}/primary`,
        payload,
        {
          preserveScroll: true,
          onFinish: () => setIsSubmitting(false),
          onSuccess: () => {
            // Update the userData in the modal with the new values
            const updatedUserData: User = {
              ...userData,
              fullname: values.companyName,
              email: values.companyEmail,
              formattedCellphone: values.companyPhone,
              info: {
                ...userData.info,
                notificationMethod: values.notificationMethod,
                language: userData.info?.language ?? "",
                timezone: userData.info?.timezone ?? "",
                currency: userData.info?.currency ?? "",
                twoFactorAuth: userData.info?.twoFactorAuth ?? "disabled",
                marketing: userData.info?.marketing ?? 0,
              } as UserInfo,
            };

            onUserDataUpdate(updatedUserData);
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
      onSubmit={handleSubmit}
      autoComplete="off"
      noValidate
    >
      <Tabs
        index={tabIndex}
        onChange={(index) => {
          setTabIndex(index);
          onTabChange?.(index);
        }}
      >
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
              labelText={t("organizational number")}
              errorText={
                errors.identityNumber?.message || serverErrors.identityNumber
              }
              isRequired
              {...register("identityNumber", {
                required: t("validation field required"),
              })}
            />
            <Input
              labelText={t("email")}
              errorText={errors.email?.message || serverErrors.email}
              isRequired
              {...register("email", {
                required: t("validation field required"),
                validate: { email: validateEmail },
              })}
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
              dialCodes={dialCodes}
              value={watch("phone1")}
              errorText={errors.phone1?.message || serverErrors.phone1}
              isRequired
              {...register("phone1", {
                required: t("validation field required"),
                validate: validatePhone,
              })}
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
                required: t("validation field required"),
                onChange: () => {
                  isLocationUpdated.current = true;
                },
              })}
              isRequired
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
                value={watch("cityId")}
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
            <Input
              labelText={t("name")}
              errorText={errors.companyName?.message || serverErrors.name}
              isRequired
              {...register("companyName", {
                required: t("validation field required"),
              })}
            />
            <Input
              type="email"
              labelText={t("user email")}
              errorText={errors.companyEmail?.message || serverErrors.email}
              isRequired
              {...register("companyEmail", {
                required: t("validation field required"),
                validate: { email: validateEmail },
              })}
            />
            <PhoneInput
              labelText={t("phone")}
              errorText={errors.companyPhone?.message || serverErrors.phone1}
              dialCodes={dialCodes}
              value={watch("companyPhone")}
              {...register("companyPhone", {
                validate: validatePhone,
              })}
            />
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
