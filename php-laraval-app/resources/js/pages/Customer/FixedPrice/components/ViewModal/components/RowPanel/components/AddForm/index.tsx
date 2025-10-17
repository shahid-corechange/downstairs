import { Button, Flex } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";

import { FIXED_PRICE_ROW_TYPES } from "@/constants/fixedPrice";
import { DEFAULT_VAT, VATS } from "@/constants/vat";

import { FixedPriceProps } from "@/pages/Customer/FixedPrice/types";

import { useGetProducts } from "@/services/product";

import FixedPrice from "@/types/fixedPrice";

import { PageProps } from "@/types";

type FormValues = {
  type: string;
  price: number;
  vatGroup: number;
  laundryProductIds?: string;
};

interface AddFormProps {
  fixedPrice: FixedPrice;
  onCancel: () => void;
  onRefetch: () => void;
}

const AddForm = ({ fixedPrice, onCancel, onRefetch }: AddFormProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage<PageProps<FixedPriceProps>>().props;
  const {
    register,
    watch,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    defaultValues: {
      vatGroup: DEFAULT_VAT,
    },
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const type = watch("type");

  const products = useGetProducts({
    request: {
      size: -1,
      show: "active",
      only: ["id", "name"],
      filter: {
        notIn: {
          "categories.id": [2, 4], // exclude miscellaneous and store
        },
      },
    },
    query: {
      staleTime: Infinity,
    },
  });

  const productOptions = useMemo(
    () =>
      products.data?.map(({ id, name }) => ({
        label: name,
        value: id,
      })) ?? [],
    [products.data],
  );

  const selectedRowTypes = useMemo(
    () => fixedPrice.rows?.map((row) => row.type) ?? [],
    [fixedPrice.rows],
  );

  const rowTypeOptions = useMemo(
    () =>
      FIXED_PRICE_ROW_TYPES.filter((value) => {
        if (fixedPrice.type === "laundry" && value === "service") {
          return false;
        } else if (fixedPrice.type === "cleaning" && value === "laundry") {
          return false;
        }
        return !selectedRowTypes.includes(value);
      }).map((value) => ({
        label: t(value),
        value: value,
      })),
    [selectedRowTypes],
  );

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);

    router.post(
      `/customers/fixedprices/${fixedPrice.id}/rows`,
      {
        ...values,
        laundryProductIds:
          type === "laundry" && values.laundryProductIds
            ? JSON.parse(values.laundryProductIds)
            : [],
        quantity: 1,
      },
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
      gap={4}
      onSubmit={handleSubmit}
      autoComplete="off"
      noValidate
    >
      <Autocomplete
        options={rowTypeOptions}
        labelText={t("type")}
        errorText={errors.type?.message || serverErrors.type}
        isRequired
        {...register("type", {
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
      <Autocomplete
        options={VATS}
        labelText={t("vat")}
        errorText={errors.vatGroup?.message || serverErrors.vatGroup}
        value={watch("vatGroup")}
        {...register("vatGroup", { required: t("validation field required") })}
        isRequired
      />
      {type === "laundry" && (
        <Autocomplete
          labelText={t("laundry product")}
          options={productOptions}
          errorText={
            errors.laundryProductIds?.message || serverErrors.laundryProductIds
          }
          value={watch("laundryProductIds")}
          {...register("laundryProductIds")}
          multiple
          maxTags={5}
        />
      )}
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
