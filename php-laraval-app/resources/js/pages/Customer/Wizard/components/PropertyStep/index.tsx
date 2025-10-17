import { Button, Flex, Textarea } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useEffect, useMemo } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import { AutocompleteOption } from "@/components/Autocomplete/types";
import Input from "@/components/Input";
import { useWizard } from "@/components/Wizard/hooks";

import { PropertyFormValues, StepsValues } from "@/pages/Customer/Wizard/types";

import { useGetKeyPlaces } from "@/services/keyplace";

const PropertyStep = () => {
  const { t } = useTranslation();

  const { errors: serverErrors } = usePage().props;

  const {
    stepsValues,
    isValidating,
    moveTo,
    onValidateSuccess,
    onValidateError,
  } = useWizard<StepsValues, PropertyFormValues>();

  const keyPlaces = useGetKeyPlaces({
    request: {
      filter: { eq: { propertyId: "null" } },
      size: -1,
    },
  });

  const {
    register,
    watch,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<PropertyFormValues>({
    defaultValues: stepsValues[2],
  });

  const keyPlaceOptions = useMemo(
    () =>
      keyPlaces.data?.reduce<AutocompleteOption[]>((acc, keyPlace) => {
        acc.push({
          label: `${keyPlace.id}`,
          value: `${keyPlace.id}`,
        });
        return acc;
      }, []) ?? [],

    [keyPlaces.data],
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
        errorText={errors.note?.message || serverErrors.note}
        resize="none"
        {...register("note")}
      />
      <Autocomplete
        options={keyPlaceOptions}
        labelText={t("key place")}
        helperText={t("key place helper text")}
        errorText={
          errors.keyPlace?.message || serverErrors["keyInformation.keyPlace"]
        }
        value={watch("keyPlace")}
        {...register("keyPlace")}
        isLoading={keyPlaces.isLoading}
        allowEmpty
      />
      <Input
        labelText={t("front door code")}
        errorText={
          errors.frontDoorCode?.message ||
          serverErrors["keyInformation.frontDoorCode"]
        }
        {...register("frontDoorCode")}
      />
      <Input
        labelText={t("alarm code off")}
        errorText={
          errors.alarmCodeOff?.message ||
          serverErrors["keyInformation.alarmCodeOff"]
        }
        {...register("alarmCodeOff")}
      />
      <Input
        labelText={t("alarm code on")}
        errorText={
          errors.alarmCodeOn?.message ||
          serverErrors["keyInformation.alarmCodeOn"]
        }
        {...register("alarmCodeOn")}
      />
      <Input
        as={Textarea}
        labelText={t("information")}
        errorText={errors.information?.message || serverErrors.note}
        {...register("information")}
        resize="none"
      />
      <Button type="submit" opacity={0} visibility="hidden" />
    </Flex>
  );
};

export default PropertyStep;
