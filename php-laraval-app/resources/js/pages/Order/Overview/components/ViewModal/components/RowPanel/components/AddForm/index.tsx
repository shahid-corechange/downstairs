import { Button, Flex, Textarea, useConst } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";

import { BOOLEAN_OPTIONS } from "@/constants/boolean";
import { ServiceMembershipType } from "@/constants/service";
import { UNITS } from "@/constants/unit";
import { VATS } from "@/constants/vat";

import Order from "@/types/order";

import { getTranslatedOptions } from "@/utils/autocomplete";

type FormValues = {
  description: string;
  price: number;
  quantity: number;
  unit: string;
  vat: number;
  hasRut: number;
  discountPercentage?: number;
  internalNote?: string;
};

interface AddFormProps {
  order: Order;
  onCancel: () => void;
  onRefetch: () => void;
}

const AddForm = ({ order, onCancel, onRefetch }: AddFormProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;
  const {
    register,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    defaultValues: {
      discountPercentage: 0,
    },
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const rutOptions = useConst(getTranslatedOptions(BOOLEAN_OPTIONS));

  const unitOptions = useConst(
    getTranslatedOptions(UNITS, (option) => ({
      label: `${t(option.label)} (${option.value})`,
      value: option.value,
    })),
  );

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);

    if (order?.customer?.membershipType === ServiceMembershipType.COMPANY) {
      values.hasRut = 0;
    }

    router.post(`/orders/${order.id}/rows`, values, {
      onFinish: () => setIsSubmitting(false),
      onSuccess: () => {
        onCancel();
        onRefetch();
      },
    });
  });

  return (
    <Flex
      as="form"
      direction="column"
      gap={4}
      onSubmit={handleSubmit}
      autoComplete="off"
      noValidate
    >
      <Input
        as={Textarea}
        labelText={t("description")}
        errorText={errors.description?.message || serverErrors.description}
        resize="none"
        isRequired
        {...register("description", {
          required: t("validation field required"),
        })}
      />
      <Flex gap={4}>
        <Input
          type="number"
          labelText={t("price")}
          helperText={t("price including vat")}
          errorText={errors.price?.message || serverErrors.price}
          min={0}
          isRequired
          {...register("price", {
            required: t("validation field required"),
            min: { value: 0, message: t("validation field min", { min: 0 }) },
            valueAsNumber: true,
          })}
        />
        <Input
          type="number"
          labelText={t("discount percentage")}
          errorText={
            errors.discountPercentage?.message ||
            serverErrors.discountPercentage
          }
          min={0}
          isRequired
          {...register("discountPercentage", {
            required: t("validation field required"),
            min: { value: 0, message: t("validation field min", { min: 0 }) },
            valueAsNumber: true,
          })}
        />
      </Flex>
      <Flex gap={4}>
        <Autocomplete
          options={unitOptions}
          labelText={t("unit")}
          errorText={errors.unit?.message || serverErrors.unit}
          isRequired
          {...register("unit", {
            required: t("validation field required"),
          })}
        />
        <Input
          type="number"
          labelText={t("quantity")}
          errorText={errors.quantity?.message || serverErrors.quantity}
          min={1}
          isRequired
          {...register("quantity", {
            required: t("validation field required"),
            min: { value: 1, message: t("validation field min", { min: 1 }) },
            valueAsNumber: true,
          })}
        />
      </Flex>
      <Flex gap={4}>
        <Autocomplete
          options={VATS}
          labelText={t("vat")}
          errorText={errors.vat?.message || serverErrors.vat}
          isRequired
          {...register("vat", { required: t("validation field required") })}
        />
        {order?.customer?.membershipType === ServiceMembershipType.PRIVATE && (
          <Autocomplete
            options={rutOptions}
            labelText={t("rut")}
            errorText={errors.hasRut?.message || serverErrors.hasRut}
            {...register("hasRut", {
              required: t("validation field required"),
            })}
            isRequired
          />
        )}
      </Flex>
      <Input
        as={Textarea}
        labelText={t("internal note")}
        errorText={errors.internalNote?.message || serverErrors.internalNote}
        resize="none"
        {...register("internalNote")}
      />
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

export default AddForm;
