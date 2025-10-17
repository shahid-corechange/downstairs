import { Button, Flex, Textarea } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";

import { BOOLEAN_OPTIONS } from "@/constants/boolean";
import { VATS } from "@/constants/vat";

import { LaundryOrder } from "@/types/laundryOrder";

import { getTranslatedOptions } from "@/utils/autocomplete";

type FormValues = {
  name: string;
  quantity: number;
  price: number;
  vat: number;
  hasRut: number;
  note?: string;
};

interface AddFormProps {
  laundryOrder: LaundryOrder;
  onCancel: () => void;
  onRefetch: () => void;
}

const AddForm = ({ laundryOrder, onCancel, onRefetch }: AddFormProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;
  const {
    register,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const rutOptions = getTranslatedOptions(BOOLEAN_OPTIONS);

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);

    if (laundryOrder?.customerType === "company") {
      values.hasRut = 0;
    }

    router.post(`/laundry-orders/${laundryOrder.id}/products`, values, {
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
        labelText={t("name")}
        errorText={errors.name?.message || serverErrors.name}
        resize="none"
        isRequired
        {...register("name", {
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
      </Flex>
      <Flex gap={4}>
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
        {laundryOrder?.customerType === "private" && (
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
        labelText={t("note")}
        errorText={errors.note?.message || serverErrors.note}
        resize="none"
        {...register("note")}
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
