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
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import Map from "@/components/Map";
import Modal from "@/components/Modal";
import PhoneInput from "@/components/PhoneInput";

import { DEFAULT_COUNTRY_ID, DEFAULT_COUNTRY_NAME } from "@/constants/location";

import { useDebounce } from "@/hooks/debounce";

import { useGetCityByCountryService } from "@/services/city";
import { useGetCountries } from "@/services/country";
import { useGetGeocodeService } from "@/services/geocode";

import { Store } from "@/types/store";
import User from "@/types/user";

import { validateEmail, validatePhone } from "@/utils/validation";

import { PageProps } from "@/types";

interface FormValues {
  name: string;
  email: string;
  phone: string;
  companyNumber: string;
  country: number;
  cityId: number;
  userIds: string;
  postalCode: string;
  address: string;
  address2: string;
  latitude: number;
  longitude: number;
}

const panelFields: (keyof FormValues)[][] = [
  ["name", "email", "companyNumber", "phone"],
  [
    "country",
    "address",
    "address2",
    "postalCode",
    "cityId",
    "latitude",
    "longitude",
  ],
];

export interface EditModalProps {
  employees: User[];
  data?: Store;
  isOpen: boolean;
  onClose: () => void;
}

const EditModal = ({ employees, onClose, isOpen, data }: EditModalProps) => {
  const { t } = useTranslation();

  const { errors: serverErrors } = usePage<PageProps>().props;
  const {
    register,
    reset,
    watch,
    setValue,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    defaultValues: {
      country: DEFAULT_COUNTRY_ID,
    },
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [countryLabel, setCountryLabel] = useState(DEFAULT_COUNTRY_NAME);
  const [cityLabel, setCityLabel] = useState("");
  const [tabIndex, setTabIndex] = useState(0);

  const countryId = watch("country");
  const postalCode = watch("postalCode");
  const address = watch("address");
  const latitude = watch("latitude", 0);
  const longitude = watch("longitude", 0);

  const debouncedAddress = useDebounce(
    { address, city: cityLabel, postalCode, country: countryLabel },
    1000,
  );

  const countries = useGetCountries({
    request: {
      only: ["id", "name", "code", "dialCode"],
    },
  });

  const cities = useGetCityByCountryService(countryId, {
    request: {
      only: ["id", "name"],
    },
  });

  const geocode = useGetGeocodeService({
    ...debouncedAddress,
    options: {
      enabled: !!address && !!cityLabel && !!postalCode && !!countryLabel,
    },
  });

  const dialCodes = useMemo(
    () => countries.data?.map((country) => `+${country.dialCode}`) ?? [],
    [countries.data],
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

  const employeeOptions = useMemo(
    () => employees.map((item) => ({ label: item.fullname, value: item.id })),
    [employees],
  );

  const handleError = (errors: (keyof FormValues)[]) => {
    const field = errors[0];
    const index = panelFields.findIndex((fields) => fields.includes(field));
    setTabIndex(index);
  };

  const handleSubmit = formSubmitHandler(
    (values) => {
      setIsSubmitting(true);
      router.patch(
        `/stores/${data?.id}`,
        {
          ...values,
          userIds: values.userIds ? JSON.parse(values.userIds) : [],
        },
        {
          onFinish: () => setIsSubmitting(false),
          onSuccess: () => onClose(),
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
      setValue("latitude", geocode.data.latitude);
      setValue("longitude", geocode.data.longitude);
    }
  }, [geocode.data]);

  useEffect(() => {
    if (isOpen) {
      setTabIndex(0);
      reset({
        name: data?.name,
        companyNumber: data?.companyNumber,
        country: data?.address?.city?.countryId,
        email: data?.email,
        phone: data?.formattedPhone,
        address: data?.address?.address,
        address2: data?.address?.address2,
        postalCode: data?.address?.postalCode,
        cityId: data?.address?.cityId,
        latitude: data?.address?.latitude,
        longitude: data?.address?.longitude,
        userIds: JSON.stringify(data?.users?.map((user) => user.id) ?? []),
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen]);

  return (
    <Modal title={t("edit store")} isOpen={isOpen} onClose={onClose}>
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
        <Tabs index={tabIndex} onChange={setTabIndex}>
          <TabList>
            <Tab>{t("store")}</Tab>
            <Tab>{t("address")}</Tab>
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
                labelText={t("organization number")}
                errorText={
                  errors.companyNumber?.message || serverErrors.companyNumber
                }
                {...register("companyNumber", {
                  required: t("validation field required"),
                })}
                isRequired
              />
              <Input
                type="email"
                labelText={t("email")}
                errorText={errors.email?.message || serverErrors.email}
                {...register("email", {
                  required: t("validation field required"),
                  validate: validateEmail,
                })}
                isRequired
              />
              <PhoneInput
                labelText={t("phone")}
                errorText={errors.phone?.message || serverErrors.phone}
                dialCodes={dialCodes}
                value={watch("phone")}
                {...register("phone", {
                  required: t("validation field required"),
                  validate: validatePhone,
                })}
                isRequired
              />
              <Autocomplete
                isRequired
                options={employeeOptions}
                labelText={`${t("employees")} (${data?.users?.length ?? 1})`}
                helperText={t("employee must assigned to cashier role")}
                value={watch("userIds")}
                errorText={errors.userIds?.message || serverErrors.userIds}
                {...register("userIds", {
                  required: t("validation field required"),
                })}
                multiple
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
                    setValue("latitude", position.lat);
                    setValue("longitude", position.lng);
                  }}
                />
              </Skeleton>
            </TabPanel>
          </TabPanels>
        </Tabs>

        <Flex justify="right" mt={4} gap={4}>
          <Button colorScheme="gray" fontSize="sm" onClick={onClose}>
            {t("close")}
          </Button>
          <Button
            type="submit"
            fontSize="sm"
            isLoading={isSubmitting}
            loadingText={t("please wait")}
          >
            {t("submit")}
          </Button>
        </Flex>
      </Flex>
    </Modal>
  );
};

export default EditModal;
