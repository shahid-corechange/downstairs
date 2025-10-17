import { Button, Flex } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useEffect, useMemo } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import PhoneInput from "@/components/PhoneInput";
import { useWizard } from "@/components/Wizard/hooks";

import { INVOICE_DUE_DAYS, INVOICE_METHODS } from "@/constants/invoice";

import locales from "@/data/locales.json";

import {
  AccountFormValues,
  CustomerWizardPageProps,
  StepsValues,
} from "@/pages/CashierCustomer/Wizard/components/PrivateWizard/types";

import { useGetCountries } from "@/services/country";

import { getTranslatedOptions } from "@/utils/autocomplete";
import { validateEmail, validatePhone } from "@/utils/validation";

import { PageProps } from "@/types";

const AccountStep = () => {
  const { t } = useTranslation();

  const {
    query,
    dueDays,
    errors: serverErrors,
  } = usePage<PageProps<CustomerWizardPageProps>>().props;

  const {
    stepsValues,
    isValidating,
    moveTo,
    onValidateSuccess,
    onValidateError,
  } = useWizard<StepsValues, AccountFormValues>();

  const {
    register,
    watch,
    setValue,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<AccountFormValues>({
    defaultValues: {
      ...stepsValues[0],
      discountPercentage: 0,
      firstName: stepsValues[0].firstName ?? query?.name,
      timezone: "Europe/Stockholm",
      language: stepsValues[0].language ?? "sv_SE",
      currency: "SEK",
      twoFactorAuth: "disabled",
      dueDays: stepsValues[0].dueDays ?? dueDays,
      invoiceMethod: "print",
    },
  });

  const email = watch("email");

  const countries = useGetCountries({
    request: {
      only: ["dialCode"],
    },
  });

  const dialCodes = useMemo(
    () => countries.data?.map((country) => `+${country.dialCode}`) ?? [],
    [countries.data],
  );

  const invoiceMethodOptions = getTranslatedOptions(INVOICE_METHODS);

  const handleSubmit = formSubmitHandler(onValidateSuccess, onValidateError);

  useEffect(() => {
    if (isValidating) {
      handleSubmit();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isValidating]);

  useEffect(() => {
    if (!email) {
      setValue("invoiceMethod", "print");
    }
  }, [email]);

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
      <Flex gap={4}>
        <Input
          labelText={t("first name")}
          errorText={errors.firstName?.message || serverErrors.firstName}
          {...register("firstName", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <Input
          labelText={t("last name")}
          errorText={errors.lastName?.message || serverErrors.lastName}
          {...register("lastName")}
        />
      </Flex>
      <Input
        labelText={t("identity number")}
        errorText={
          errors.identityNumber?.message || serverErrors.identityNumber
        }
        {...register("identityNumber")}
      />
      <Input
        type="email"
        labelText={t("email")}
        errorText={errors.email?.message || serverErrors.email}
        {...register("email", {
          validate: {
            email: (value) => !value || validateEmail(value),
          },
        })}
      />
      <Input
        labelText={t("discount percentage")}
        errorText={
          errors.discountPercentage?.message || serverErrors.discountPercentage
        }
        type="number"
        {...register("discountPercentage", {
          min: { value: 0, message: t("validation field min", { min: 0 }) },
          max: { value: 100, message: t("validation field max", { max: 100 }) },
          valueAsNumber: true,
        })}
        suffix="%"
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
        errorText={errors.invoiceMethod?.message || serverErrors.invoiceMethod}
        value={watch("invoiceMethod")}
        {...register("invoiceMethod", {
          required: t("validation field required"),
        })}
        isRequired
        isReadOnly={!email}
      />

      <PhoneInput
        labelText={t("phone")}
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
          {...register("language", {
            required: t("validation field required"),
          })}
          isRequired
        />
      </Flex>
      <Input
        labelText={t("currency")}
        defaultValue={watch("currency")}
        isRequired
        isDisabled
      />
      <Input
        labelText={t("two factor authentication")}
        defaultValue={t(watch("twoFactorAuth"))}
        isRequired
        isDisabled
      />
      <Button type="submit" opacity={0} visibility="hidden" />
    </Flex>
  );
};

export default AccountStep;
