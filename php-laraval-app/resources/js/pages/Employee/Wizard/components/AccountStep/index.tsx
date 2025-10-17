import { Button, Flex } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useEffect, useMemo } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import PhoneInput from "@/components/PhoneInput";
import { useWizard } from "@/components/Wizard/hooks";

import { TWO_FACTOR_OPTIONS } from "@/constants/2fa";

import locales from "@/data/locales.json";

import {
  AccountFormValues,
  EmployeeWizardPageProps,
  StepsValues,
} from "@/pages/Employee/Wizard/types";

import { useGetCountries } from "@/services/country";

import { getTranslatedOptions } from "@/utils/autocomplete";
import { validateEmail, validatePhone } from "@/utils/validation";

import { PageProps } from "@/types";

const AccountStep = () => {
  const { t } = useTranslation();

  const { roles, errors: serverErrors } =
    usePage<PageProps<EmployeeWizardPageProps>>().props;

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
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<AccountFormValues>({
    defaultValues: {
      ...stepsValues[0],
      timezone: "Europe/Stockholm",
      language: stepsValues[0].language ?? "sv_SE",
      currency: "SEK",
      twoFactorAuth: "disabled",
    },
  });

  const twoFactorOptions = getTranslatedOptions(
    TWO_FACTOR_OPTIONS as unknown as string[],
  );

  const countries = useGetCountries({
    request: {
      only: ["dialCode"],
    },
  });

  const dialCodes = useMemo(
    () => countries.data?.map((country) => `+${country.dialCode}`) ?? [],
    [countries.data],
  );

  const rolesOptions = useMemo(
    () =>
      roles.map((role) => ({
        label: role,
        value: role,
      })),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [],
  );

  const handleSubmit = formSubmitHandler(onValidateSuccess, onValidateError);

  useEffect(() => {
    if (isValidating) {
      handleSubmit();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
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
          {...register("lastName", {
            required: t("validation field required"),
          })}
          isRequired
        />
      </Flex>
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
      <Input
        type="email"
        labelText={t("email")}
        errorText={errors.email?.message || serverErrors.email}
        {...register("email", {
          required: t("validation field required"),
          validate: { email: validateEmail },
        })}
        isRequired
      />
      <PhoneInput
        dialCodes={dialCodes}
        labelText={t("phone")}
        errorText={errors.cellphone?.message || serverErrors.cellphone}
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
      <Autocomplete
        options={twoFactorOptions}
        labelText={t("two factor authentication")}
        helperText={t("two factor authentication helper text")}
        value={watch("twoFactorAuth")}
        errorText={errors.twoFactorAuth?.message || serverErrors.twoFactorAuth}
        {...register("twoFactorAuth", {
          required: t("validation field required"),
        })}
        isRequired
      />
      <Autocomplete
        options={rolesOptions}
        labelText={t("role")}
        errorText={errors.roles?.message || serverErrors.roles}
        value={watch("roles")}
        placeholderTags={["Employee"]}
        {...register("roles")}
        multiple
      />
      <Button type="submit" opacity={0} visibility="hidden" />
    </Flex>
  );
};

export default AccountStep;
