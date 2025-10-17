import { Button, Flex, Textarea } from "@chakra-ui/react";
import { useEffect, useMemo } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import Modal from "@/components/Modal";

import { DEFAULT_VAT, VATS } from "@/constants/vat";

import {
  AddCustomCartProductFormValues,
  AddCustomCartProductModalData,
} from "@/types/customCartProduct";

export interface AddCustomCartProductModalProps {
  data?: AddCustomCartProductModalData;
  isOpen: boolean;
  onClose: () => void;
  handleAddCustomCartProductSubmit: (
    values: AddCustomCartProductFormValues,
  ) => void;
}

const AddCustomCartProductModal = ({
  data,
  onClose,
  isOpen,
  handleAddCustomCartProductSubmit,
}: AddCustomCartProductModalProps) => {
  const { t } = useTranslation();

  const {
    register,
    reset,
    watch,
    setValue,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<AddCustomCartProductFormValues>({
    defaultValues: {
      vatGroup: DEFAULT_VAT,
    },
  });

  const priceWithVat = watch("priceWithVat");
  const quantity = watch("quantity");
  const discount = watch("discount");

  const totalPrice = useMemo(() => {
    if (!priceWithVat || !quantity) {
      return 0;
    }

    const price = priceWithVat * quantity * (1 - (discount || 0) / 100);
    setValue("totalPrice", price);

    return price;
  }, [priceWithVat, quantity, discount, setValue]);

  const handleSubmit = formSubmitHandler((values) => {
    handleAddCustomCartProductSubmit({
      ...values,
      discount: values.discount || 0,
    });
    onClose();
  });

  useEffect(() => {
    if (isOpen) {
      reset({
        name: "",
        priceWithVat: 0,
        vatGroup: DEFAULT_VAT,
        quantity: data?.quantity || 1,
        discount: 0,
        totalPrice: 0,
      });
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen]);

  return (
    <Modal
      title={t("product sale miscellaneous")}
      onClose={onClose}
      isOpen={isOpen}
      size="lg"
    >
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
        <Input
          labelText={t("name")}
          errorText={errors.name?.message}
          {...register("name", {
            required: t("validation field required"),
            validate: (value) => {
              const existingProduct = data?.products?.find(
                (product) => product.name === value,
              );

              if (existingProduct) {
                return t("product already exists");
              }

              return true;
            },
          })}
          isRequired
        />
        <Input
          labelText={t("quantity")}
          errorText={errors.quantity?.message}
          type="number"
          min={1}
          {...register("quantity", {
            valueAsNumber: true,
            min: { value: 1, message: t("validation field min", { min: 1 }) },
            required: t("validation field required"),
          })}
          isRequired
        />
        <Input
          labelText={t("price")}
          helperText={t("price including vat")}
          type="number"
          min={0}
          errorText={errors.priceWithVat?.message}
          {...register("priceWithVat", {
            valueAsNumber: true,
            min: { value: 0, message: t("validation field min", { min: 0 }) },
            required: t("validation field required"),
          })}
          isRequired
        />
        <Autocomplete
          options={VATS}
          labelText={t("vat")}
          errorText={errors.vatGroup?.message}
          value={watch("vatGroup")}
          {...register("vatGroup", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <Input
          labelText={t("discount percentage")}
          errorText={errors.discount?.message}
          type="number"
          min={0}
          max={100}
          {...register("discount", {
            valueAsNumber: true,
            min: { value: 0, message: t("validation field min", { min: 0 }) },
            max: {
              value: 100,
              message: t("validation field max", { max: 100 }),
            },
          })}
          suffix="%"
        />
        <Input
          labelText={t("total price")}
          value={totalPrice}
          errorText={errors.totalPrice?.message}
          type="number"
          {...register("totalPrice", {
            valueAsNumber: true,
            required: t("validation field required"),
          })}
          isReadOnly
          isRequired
        />
        <Input
          as={Textarea}
          labelText={t("note")}
          errorText={errors.note?.message}
          {...register("note")}
        />
        <Flex justify="right" mt={4} gap={4}>
          <Button colorScheme="gray" fontSize="sm" onClick={onClose}>
            {t("close")}
          </Button>
          <Button type="submit" fontSize="sm" loadingText={t("please wait")}>
            {t("add")}
          </Button>
        </Flex>
      </Flex>
    </Modal>
  );
};

export default AddCustomCartProductModal;
