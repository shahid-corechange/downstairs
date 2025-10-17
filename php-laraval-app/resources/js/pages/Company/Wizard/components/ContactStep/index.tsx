import { Button, Flex } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useEffect, useMemo } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Input from "@/components/Input";
import PhoneInput from "@/components/PhoneInput";
import { useWizard } from "@/components/Wizard/hooks";

import { ContactFormValues, StepsValues } from "@/pages/Company/Wizard/types";

import { useGetCountries } from "@/services/country";

import { validateEmail, validatePhone } from "@/utils/validation";

const ContactStep = () => {
  const { t } = useTranslation();

  const { errors: serverErrors } = usePage().props;

  const {
    stepsValues,
    isValidating,
    moveTo,
    onValidateSuccess,
    onValidateError,
  } = useWizard<StepsValues, ContactFormValues>();

  const {
    register,
    watch,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<ContactFormValues>({
    defaultValues: stepsValues[1],
  });

  const countries = useGetCountries({
    request: {
      only: ["dialCode"],
    },
  });

  const dialCodes = useMemo(
    () => countries.data?.map((country) => `+${country.dialCode}`) ?? [],
    [countries.data],
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
        {...register("identityNumber")}
      />
      <Input
        type="email"
        labelText={t("email")}
        errorText={errors.email?.message || serverErrors.email}
        {...register("email", {
          validate: (email) => !email || validateEmail(email),
        })}
      />
      <PhoneInput
        labelText={t("phone")}
        helperText={t("contact person cellphone helper text")}
        dialCodes={dialCodes}
        errorText={errors.cellphone?.message || serverErrors.cellphone}
        value={watch("cellphone")}
        {...register("cellphone", {
          validate: (phone) => !phone || validatePhone(phone),
        })}
      />
      <Button type="submit" opacity={0} visibility="hidden" />
    </Flex>
  );
};

export default ContactStep;
