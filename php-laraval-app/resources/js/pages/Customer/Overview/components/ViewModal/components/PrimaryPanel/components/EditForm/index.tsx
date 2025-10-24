import {
  AlertDialog,
  AlertDialogBody,
  AlertDialogContent,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogOverlay,
  Button,
  Flex,
  Skeleton,
  Tab,
  TabList,
  TabPanel,
  TabPanels,
  Tabs,
  useDisclosure,
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

import locales from "@/data/locales.json";

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
  // User info fields
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
  userData: User;
  customer: Customer;
  onCancel: () => void;
  onRefetch: () => void;
  onUserDataUpdate: (updatedUser: User) => void;
  onTabChange?: (tabIndex: number) => void;
}

const EditForm = ({
  userId,
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
      address: customer.address?.address,
      address2: customer.address?.address2,
      email: customer.email,
      phone1: customer.formattedPhone1,
      dueDays: customer.dueDays,
      invoiceMethod: customer.invoiceMethod,
      country: customer.address?.city?.countryId,
      cityId: customer.address?.cityId,
      postalCode: customer.address?.postalCode,
      // User info defaults
      firstName: userData.firstName,
      lastName: userData.lastName,
      userEmail: userData.email,
      cellphone: userData.formattedCellphone,
      language: userData.info?.language ?? "",
      timezone: userData.info?.timezone ?? "",
      notificationMethod: userData.info?.notificationMethod ?? "",
      status: userData.status,
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
  const formRef = useRef<HTMLFormElement>(null);

  // Track initial values for confirmation check
  const initialValuesRef = useRef({
    address: customer.address?.address || "",
    email: customer.email || "",
    identityNumber: customer.identityNumber || "",
  });

  // Track if confirmation has been shown and accepted
  const [hasConfirmed, setHasConfirmed] = useState(false);

  // Confirmation dialog state
  const {
    isOpen: isConfirmOpen,
    onOpen: onConfirmOpen,
    onClose: onConfirmClose,
  } = useDisclosure();
  const cancelRef = useRef<HTMLButtonElement>(null);

  const countryId = watch("country");
  const postalCode = watch("postalCode");
  const address = watch("address");
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

  // exclude email notification method if email is not provided
  const notificationMethodOptions = useMemo(
    () =>
      getTranslatedOptions(NOTIFICATION_METHOD_OPTIONS).filter((option) =>
        !userEmail ? option.value !== "email" : true,
      ),
    [userEmail],
  );

  const statusOptions = useMemo(
    () =>
      getTranslatedOptions([
        "active",
        "inactive",
        "suspended",
        "pending",
        "blocked",
      ]),
    [],
  );

  const handleError = (errors: (keyof FormValues)[]) => {
    const field = errors[0];
    const index = panelFields.findIndex((fields) => fields.includes(field));
    setTabIndex(index);
  };

  // Function to actually perform the submission
  const performSubmit = (values: FormValues) => {
    setIsSubmitting(true);

    const payload = {
      ...values,
      membershipType: "private",
      latitude,
      longitude,
      userEmail: values.userEmail ?? null,
    };

    router.patch(
      `/customers/${userId}/addresses/${customer.id}/primary`,
      payload,
      {
        preserveScroll: true,
        onFinish: () => setIsSubmitting(false),
        onSuccess: () => {
          // Update the userData in the modal with the new values
          const updatedUserData: User = {
            ...userData,
            firstName: values.firstName,
            lastName: values.lastName,
            email: values.userEmail,
            formattedCellphone: values.cellphone,
            status: values.status as User["status"],
            info: {
              ...userData.info,
              language: values.language,
              timezone: values.timezone,
              notificationMethod: values.notificationMethod,
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
  };

  const handleSubmit = formSubmitHandler(
    (values) => {
      performSubmit(values);
    },
    (errors) => {
      handleError(Object.keys(errors) as (keyof FormValues)[]);
    },
  );

  // Handle form submit button click - intercept before validation
  const handleFormSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    // Check if any required fields were initially empty and user hasn't confirmed yet
    const hasEmptyInitialFields =
      !initialValuesRef.current.address ||
      !initialValuesRef.current.email ||
      !initialValuesRef.current.identityNumber;

    if (hasEmptyInitialFields && !hasConfirmed) {
      // Show confirmation dialog first, before validation
      onConfirmOpen();
    } else {
      // Proceed with normal form validation and submission
      handleSubmit(e);
    }
  };

  // Handle confirmation dialog confirmation
  const handleConfirmSubmit = () => {
    setHasConfirmed(true);
    onConfirmClose();

    // Trigger form validation after confirmation
    // Use setTimeout to ensure state is updated, then trigger submit programmatically
    setTimeout(() => {
      if (formRef.current) {
        formRef.current.dispatchEvent(
          new Event("submit", { cancelable: true, bubbles: true }),
        );
      }
    }, 0);
  };

  useEffect(() => {
    if (geocode.data) {
      setLatitude(geocode.data.latitude);
      setLongitude(geocode.data.longitude);
    }
  }, [geocode.data]);

  return (
    <Flex
      ref={formRef}
      as="form"
      direction="column"
      onSubmit={handleFormSubmit}
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
              labelText={t("identity number")}
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
              errorText={errors.phone1?.message || serverErrors.phone1}
              dialCodes={dialCodes}
              value={watch("phone1")}
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
                isRequired
                {...register("lastName", {
                  required: t("validation field required"),
                })}
              />
            </Flex>

            <Input
              labelText={t("user email")}
              errorText={errors.userEmail?.message || serverErrors.userEmail}
              {...register("userEmail", {
                required: t("validation field required"),
              })}
              type="email"
              isRequired
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
                value={watch("timezone")}
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

      <AlertDialog
        isOpen={isConfirmOpen}
        leastDestructiveRef={cancelRef}
        onClose={onConfirmClose}
      >
        <AlertDialogOverlay>
          <AlertDialogContent>
            <AlertDialogHeader fontSize="lg" fontWeight="bold">
              {t("incomplete customer information")}
            </AlertDialogHeader>

            <AlertDialogBody>{t("this is laundry customer")}</AlertDialogBody>

            <AlertDialogFooter>
              <Button ref={cancelRef} onClick={onConfirmClose}>
                {t("close")}
              </Button>
              <Button
                colorScheme="blue"
                onClick={handleConfirmSubmit}
                ml={3}
                isLoading={isSubmitting}
              >
                {t("okay")}
              </Button>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialogOverlay>
      </AlertDialog>
    </Flex>
  );
};

export default EditForm;
