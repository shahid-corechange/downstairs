import { Button, Flex, TabPanel, TabPanels, Tabs } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Input from "@/components/Input";
import PhoneInput from "@/components/PhoneInput";

import { useGetCountries } from "@/services/country";

import { RutCoApplicant } from "@/types/rutCoApplicant";

import { validatePhone } from "@/utils/validation";

type FormValues = {
  name: string;
  identityNumber: string;
  phone: string;
};

interface EditFormProps {
  userId: number;
  rutCoApplicant: RutCoApplicant;
  onCancel: () => void;
  onRefetch: () => void;
}

const EditForm = ({
  userId,
  rutCoApplicant,
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
      name: rutCoApplicant.name,
      identityNumber: rutCoApplicant.identityNumber,
      phone: rutCoApplicant.formattedPhone,
    },
  });

  const [isSubmitting, setIsSubmitting] = useState(false);

  const countries = useGetCountries({
    request: {
      only: ["dialCode"],
    },
  });
  const dialCodes = useMemo(
    () => countries.data?.map((country) => `+${country.dialCode}`) ?? [],
    [countries.data],
  );

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);
    router.patch(
      `/customers/${userId}/rut-co-applicants/${rutCoApplicant.id}`,
      values,
      {
        onFinish: () => setIsSubmitting(false),
        onSuccess: () => {
          onCancel();
          onRefetch();
        },
      },
    );
  });

  return (
    <Flex
      as="form"
      direction="column"
      onSubmit={handleSubmit}
      autoComplete="off"
      noValidate
    >
      <Tabs>
        <TabPanels>
          <TabPanel display="flex" flexDirection="column" gap={4} py={6}>
            <Input
              labelText={t("name")}
              errorText={errors.name?.message || serverErrors.name}
              {...register("name", {
                required: t("validation field required"),
              })}
              isRequired
            />
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
