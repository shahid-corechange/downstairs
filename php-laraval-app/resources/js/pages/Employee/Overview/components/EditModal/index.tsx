import { Button, Flex, useConst } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import Modal from "@/components/Modal";
import PhoneInput from "@/components/PhoneInput";

import locales from "@/data/locales.json";

import { useGetCountries } from "@/services/country";

import User from "@/types/user";

import { validateEmail, validatePhone } from "@/utils/validation";

type FormValues = {
  firstName: string;
  lastName: string;
  identityNumber: string;
  email: string;
  cellphone: string;
  language: string;
  timezone: string;
  status: string;
};

export interface EditModalProps {
  isOpen: boolean;
  onClose: () => void;
  data?: User;
}

const EditModal = ({ data, onClose, isOpen }: EditModalProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;

  const {
    register,
    reset,
    watch,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const countries = useGetCountries({
    request: {
      only: ["dialCode"],
    },
    query: {
      enabled: isOpen,
      staleTime: Infinity,
    },
  });

  const dialCodes = useMemo(
    () => countries.data?.map((country) => `+${country.dialCode}`) ?? [],
    [countries.data],
  );

  const statusOptions = useConst([
    {
      label: t("active"),
      value: "active",
    },
    {
      label: t("inactive"),
      value: "inactive",
    },
    {
      label: t("suspended"),
      value: "suspended",
    },
    {
      label: t("pending"),
      value: "pending",
    },
    {
      label: t("blocked"),
      value: "blocked",
    },
  ]);

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);
    router.patch(`/employees/${data?.id}`, values, {
      onFinish: () => setIsSubmitting(false),
      onSuccess: onClose,
    });
  });

  useEffect(() => {
    reset({
      firstName: data?.firstName,
      lastName: data?.lastName,
      identityNumber: data?.identityNumber,
      email: data?.email,
      cellphone: data?.formattedCellphone,
      language: data?.info?.language,
      timezone: data?.info?.timezone,
      status: data?.status,
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data]);

  return (
    <Modal title={t("edit employee")} isOpen={isOpen} onClose={onClose}>
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
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
          type="email"
          labelText={t("email")}
          errorText={errors.email?.message || serverErrors.email}
          isRequired
          {...register("email", {
            required: t("validation field required"),
            validate: { email: validateEmail },
          })}
        />
        <PhoneInput
          labelText={t("phone")}
          errorText={errors.cellphone?.message || serverErrors.cellphone}
          dialCodes={dialCodes}
          value={watch("cellphone")}
          isRequired
          {...register("cellphone", {
            required: t("validation field required"),
            validate: validatePhone,
          })}
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
          options={statusOptions}
          labelText={t("status")}
          errorText={errors.status?.message || serverErrors.status}
          value={watch("status")}
          isRequired
          {...register("status", { required: t("validation field required") })}
        />
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
