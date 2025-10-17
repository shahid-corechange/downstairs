import { Button, Flex, Textarea } from "@chakra-ui/react";
import { useEffect, useMemo } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Input from "@/components/Input";
import Modal from "@/components/Modal";

import { RUT_DISCOUNT } from "@/constants/rut";
import { DEFAULT_VAT } from "@/constants/vat";

import {
  CartProductModalData,
  EditCartProductFormValues,
} from "@/types/cartProduct";

interface EditCartProductModalProps {
  data?: CartProductModalData;
  isOpen: boolean;
  onClose: () => void;
  handleProductChangeSubmit: (
    index: number,
    values: EditCartProductFormValues,
  ) => void;
}

const EditCartProductModal = ({
  data,
  onClose,
  isOpen,
  handleProductChangeSubmit,
}: EditCartProductModalProps) => {
  const { t } = useTranslation();

  const {
    register,
    reset,
    watch,
    setValue,
    setFocus,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<EditCartProductFormValues>();

  const priceWithVat = watch("priceWithVat");
  const quantity = watch("quantity");
  const discount = watch("discount");

  const totalPrice = useMemo(() => {
    if (priceWithVat === 0) {
      return 0;
    }

    if (!priceWithVat || !quantity) {
      return data?.product.totalPrice || 0;
    }

    const price = priceWithVat * quantity * (1 - (discount || 0) / 100);
    return data?.product.hasRut ? price * RUT_DISCOUNT : price;
  }, [
    priceWithVat,
    quantity,
    discount,
    data?.product.hasRut,
    data?.product.totalPrice,
  ]);

  const handleSubmit = formSubmitHandler((values) => {
    if (!data) {
      return;
    }

    handleProductChangeSubmit(data.index, {
      ...values,
      discount: values.discount || 0,
    });
  });

  useEffect(() => {
    setValue("totalPrice", totalPrice);
  }, [totalPrice, setValue]);

  useEffect(() => {
    if (!data?.key) {
      return;
    }

    reset({
      name: data.product?.name || "",
      priceWithVat: data.product?.isFixedPrice
        ? 0
        : data.product?.priceWithVat || 0,
      vatGroup: data.product?.vatGroup || DEFAULT_VAT,
      quantity: data.product?.quantity || 1,
      discount: data.product?.discount || 0,
      totalPrice: data?.product?.totalPrice || 0,
      note: data.product?.note || "",
    });

    const timer = setTimeout(() => {
      setFocus(data.key);
    }, 100);

    return () => clearTimeout(timer);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data]);

  return (
    <Modal
      title={t("edit product")}
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
              if (data?.product.id !== 0) {
                return true;
              }

              if (value === data?.product.name) {
                return true;
              }

              const existingProduct = data?.cartProducts?.find(
                (product) => product.name === value,
              );

              if (existingProduct) {
                return t("product already exists");
              }

              return true;
            },
          })}
          isRequired
          isReadOnly={data?.product.isFixedPrice}
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
          isReadOnly={data?.product.isFixedPrice}
        />
        <Input
          labelText={t("vat")}
          defaultValue={watch("vatGroup")}
          isRequired
          isReadOnly
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
          isReadOnly={data?.product.isFixedPrice}
        />
        <Input
          labelText={t("total price")}
          value={totalPrice}
          helperText={
            data?.product.hasRut ? t("total price including rut") : ""
          }
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
            {t("save")}
          </Button>
        </Flex>
      </Flex>
    </Modal>
  );
};

export default EditCartProductModal;
