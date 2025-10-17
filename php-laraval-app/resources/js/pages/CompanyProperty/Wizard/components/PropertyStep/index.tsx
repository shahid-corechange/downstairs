import { Button, Flex, Textarea } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useEffect, useMemo } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import { useWizard } from "@/components/Wizard/hooks";

import {
  CompanyPropertyWizardPageProps,
  PropertyFormValues,
  StepsValues,
} from "@/pages/CompanyProperty/Wizard/types";

import { PageProps } from "@/types";

const PropertyStep = () => {
  const { t } = useTranslation();

  const { propertyTypes, errors: serverErrors } =
    usePage<PageProps<CompanyPropertyWizardPageProps>>().props;

  const {
    stepsValues,
    isValidating,
    moveTo,
    onValidateSuccess,
    onValidateError,
  } = useWizard<StepsValues, PropertyFormValues>();

  const {
    register,
    watch,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<PropertyFormValues>({
    defaultValues: stepsValues[1],
  });

  const propertyTypeOptions = useMemo(
    () =>
      propertyTypes.map((item) => ({
        label: item.name,
        value: item.id,
      })),
    [propertyTypes],
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
      <Autocomplete
        options={propertyTypeOptions}
        labelText={t("property type")}
        errorText={
          errors.propertyTypeId?.message || serverErrors.propertyTypeId
        }
        value={watch("propertyTypeId")}
        {...register("propertyTypeId", {
          required: t("validation field required"),
          valueAsNumber: true,
        })}
        isRequired
      />
      <Input
        type="number"
        min={1}
        labelText={t("square meter")}
        errorText={errors.squareMeter?.message || serverErrors.squareMeter}
        {...register("squareMeter", {
          required: t("validation field required"),
          min: { value: 1, message: t("validation field min", { min: 1 }) },
          valueAsNumber: true,
        })}
        isRequired
      />
      <Input
        as={Textarea}
        labelText={t("note")}
        helperText={t("property note helper text")}
        errorText={errors.note?.message || serverErrors.note}
        {...register("note")}
        resize="none"
      />
      <Button type="submit" opacity={0} visibility="hidden" />
    </Flex>
  );
};

export default PropertyStep;
