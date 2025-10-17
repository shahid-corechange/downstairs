import { Button, Flex, useConst } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import Modal from "@/components/Modal";
import PhoneInput from "@/components/PhoneInput";

import { NOTIFICATION_METHOD_OPTIONS } from "@/constants/notification";
import { DEFAULT_NOTIFICATION_METHOD } from "@/constants/user";

import { useGetCountries } from "@/services/country";

import Customer from "@/types/customer";
import User from "@/types/user";

import { getTranslatedOptions } from "@/utils/autocomplete";
import { validateEmail, validatePhone } from "@/utils/validation";

type FormValues = {
  name: string;
  identityNumber: string;
  email: string;
  phone1: string;
  notificationMethod: string;
};

export interface EditModalProps {
  isOpen: boolean;
  onClose: () => void;
  data?: Customer;
  user?: User;
}

const EditModal = ({ data, user, onClose, isOpen }: EditModalProps) => {
  const { t } = useTranslation();

  const { errors: serverErrors } = usePage().props;
  const {
    register,
    watch,
    reset,
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

  const notificationMethodOptions = useConst(
    getTranslatedOptions(NOTIFICATION_METHOD_OPTIONS),
  );

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);
    router.patch(`/companies/${data?.id}`, values, {
      onFinish: () => {
        setIsSubmitting(false);
      },
      onSuccess: () => {
        onClose();
      },
    });
  });

  useEffect(() => {
    if (isOpen && data && user) {
      reset({
        name: data.name,
        identityNumber: data.identityNumber,
        email: data.email,
        phone1: data.formattedPhone1,
        notificationMethod:
          user?.info?.notificationMethod || DEFAULT_NOTIFICATION_METHOD,
      });
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen, data, user]);

  return (
    <Modal title={t("edit company")} onClose={onClose} isOpen={isOpen}>
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
        <Input
          labelText={t("company name")}
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
          errorText={errors.phone1?.message || serverErrors.phone1}
          dialCodes={dialCodes}
          value={watch("phone1")}
          {...register("phone1", {
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
