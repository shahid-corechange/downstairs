import { Button, Flex, Text, Textarea, useConst } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import ColorPicker from "@/components/ColorPicker";
import FileInput from "@/components/FileInput";
import Input from "@/components/Input";
import Modal from "@/components/Modal";

import { BOOLEAN_OPTIONS } from "@/constants/boolean";
import { ServiceMembershipType } from "@/constants/service";
import { UNITS } from "@/constants/unit";

import Addon from "@/types/addon";
import Category from "@/types/category";
import Product from "@/types/product";
import Service from "@/types/service";

import { getTranslatedOptions } from "@/utils/autocomplete";
import { getColor } from "@/utils/color";
import { validateFilesExtension, validateFilesSize } from "@/utils/validation";

type FormValues = {
  name: {
    sv_SE: string;
    en_US: string;
  };
  description: {
    sv_SE: string;
    en_US: string;
  };
  serviceIds: string;
  categoryIds: string;
  productIds: string;
  unit: string;
  price: number;
  creditPrice: number;
  vatGroup: number;
  hasRut: number;
  color: string;
  thumbnailImage?: FileList;
  thumbnail?: File;
};

export interface EditModalProps {
  isOpen: boolean;
  onClose: () => void;
  data?: Addon;
  services: Service[];
  categories: Category[];
  products: Product[];
}

const EditModal = ({
  data,
  services,
  categories,
  products,
  onClose,
  isOpen,
}: EditModalProps) => {
  const { t } = useTranslation();

  const { errors: serverErrors } = usePage().props;
  const {
    register,
    reset,
    watch,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const hasPrivateService = useMemo(
    () =>
      (data?.services ?? []).some(
        (service) => service.membershipType === ServiceMembershipType.PRIVATE,
      ),
    [data?.services],
  );

  const unitOptions = useConst(
    getTranslatedOptions(UNITS, (option) => ({
      label: `${t(option.label)} (${option.value})`,
      value: option.value,
    })),
  );

  const serviceOptions = useMemo(
    () =>
      services.map((item) => ({
        label: item.name,
        value: item.id,
      })),
    [services],
  );

  const categoryOptions = useMemo(
    () =>
      categories.map((item) => ({
        label: item.name,
        value: item.id,
      })),
    [categories],
  );

  const productOptions = useMemo(
    () =>
      products.map((item) => ({
        label: item.name,
        value: item.id,
      })),
    [products],
  );

  const rutOptions = useConst(getTranslatedOptions(BOOLEAN_OPTIONS));

  const handleSubmit = formSubmitHandler(
    ({
      thumbnailImage,
      categoryIds,
      serviceIds,
      productIds,
      hasRut,
      ...values
    }) => {
      setIsSubmitting(true);

      if (thumbnailImage && thumbnailImage.length > 0) {
        const file = thumbnailImage[0];
        values.thumbnail = file;
      }

      const newValues = {
        ...values,
        categoryIds: JSON.parse(categoryIds),
        serviceIds: JSON.parse(serviceIds),
        productIds: productIds ? JSON.parse(productIds) : [],
        hasRut: hasPrivateService ? !!hasRut : false,
      };

      router.post(
        `/addons/${data?.id}`,
        { _method: "PATCH", ...newValues },
        {
          onFinish: () => setIsSubmitting(false),
          onSuccess: () => onClose(),
        },
      );
    },
  );

  useEffect(() => {
    reset({
      name: {
        sv_SE: data?.translations?.sv_SE?.name?.value,
        en_US: data?.translations?.en_US?.name?.value,
      },
      description: {
        sv_SE: data?.translations?.sv_SE?.description?.value,
        en_US: data?.translations?.en_US?.description?.value,
      },
      serviceIds: JSON.stringify((data?.services || []).map((item) => item.id)),
      categoryIds: JSON.stringify(
        (data?.categories || []).map((item) => item.id),
      ),
      productIds: JSON.stringify((data?.products || []).map((item) => item.id)),
      unit: data?.unit,
      price: data?.priceWithVat,
      creditPrice: data?.creditPrice,
      vatGroup: data?.vatGroup,
      hasRut: Number(data?.hasRut),
      color: data?.color ?? getColor("gray.500"),
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data]);

  return (
    <Modal title={t("edit add on")} onClose={onClose} isOpen={isOpen}>
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
        <Flex gap={4}>
          <Flex
            as="fieldset"
            direction="column"
            p={4}
            border="1px"
            borderColor="inherit"
            rounded="md"
            flex={1}
            gap={4}
          >
            <Text as="legend" fontSize="sm" px={1}>
              {t("language sv_se")}
            </Text>
            <Input
              labelText={t("name")}
              errorText={
                errors.name?.sv_SE?.message || serverErrors["name.sv_SE"]
              }
              {...register("name.sv_SE", {
                required: t("validation field required"),
              })}
              isRequired
            />
            <Input
              as={Textarea}
              labelText={t("description")}
              errorText={
                errors.description?.sv_SE?.message ||
                serverErrors["description.sv_SE"]
              }
              resize="none"
              {...register("description.sv_SE", {
                required: t("validation field required"),
              })}
              isRequired
            />
          </Flex>
          <Flex
            as="fieldset"
            direction="column"
            p={4}
            border="1px"
            borderColor="inherit"
            rounded="md"
            flex={1}
            gap={4}
          >
            <Text as="legend" fontSize="sm" px={1}>
              {t("language en_us")}
            </Text>
            <Input
              labelText={t("name")}
              errorText={
                errors.name?.en_US?.message || serverErrors["name.en_US"]
              }
              {...register("name.en_US", {
                required: t("validation field required"),
              })}
              isRequired
            />
            <Input
              as={Textarea}
              labelText={t("description")}
              errorText={
                errors.description?.en_US?.message ||
                serverErrors["description.en_US"]
              }
              resize="none"
              {...register("description.en_US", {
                required: t("validation field required"),
              })}
              isRequired
            />
          </Flex>
        </Flex>
        <Autocomplete
          options={serviceOptions}
          labelText={t("services")}
          errorText={errors.serviceIds?.message || serverErrors.serviceIds}
          value={watch("serviceIds")}
          {...register("serviceIds", {
            required: t("validation field required"),
          })}
          isRequired
          multiple
        />
        <Autocomplete
          options={categoryOptions}
          labelText={t("categories")}
          errorText={errors.categoryIds?.message || serverErrors.categoryIds}
          value={watch("categoryIds")}
          {...register("categoryIds", {
            required: t("validation field required"),
          })}
          isRequired
          multiple
        />
        <Autocomplete
          options={productOptions}
          labelText={t("products")}
          errorText={errors.productIds?.message || serverErrors.productIds}
          value={watch("productIds")}
          {...register("productIds")}
          multiple
        />
        <Autocomplete
          options={unitOptions}
          labelText={t("unit")}
          errorText={errors.unit?.message || serverErrors.unit}
          value={watch("unit")}
          {...register("unit", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <Flex gap={4}>
          <Input
            labelText={t("price")}
            helperText={t("price including vat")}
            type="number"
            min={0}
            errorText={errors.price?.message || serverErrors.price}
            {...register("price", {
              required: t("validation field required"),
              valueAsNumber: true,
            })}
            isRequired
          />
          <Input
            labelText={t("credit price")}
            type="number"
            min={0}
            errorText={errors.creditPrice?.message || serverErrors.creditPrice}
            {...register("creditPrice", {
              required: t("validation field required"),
              valueAsNumber: true,
            })}
            isRequired
          />
        </Flex>
        <Flex gap={4}>
          <Input
            labelText={t("vat")}
            defaultValue={watch("vatGroup")}
            isRequired
            isReadOnly
          />
          {hasPrivateService && (
            <Autocomplete
              options={rutOptions}
              labelText={t("rut")}
              errorText={errors.hasRut?.message || serverErrors.hasRut}
              value={watch("hasRut")}
              {...register("hasRut", {
                required: t("validation field required"),
              })}
              isRequired
            />
          )}
        </Flex>
        <ColorPicker
          labelText={t("color")}
          value={watch("color")}
          errorText={errors.color?.message || serverErrors.color}
          {...register("color", { required: t("validation field required") })}
          isRequired
        />
        <FileInput
          labelText={t("thumbnail")}
          previewContainer={{ bg: "gray.500" }}
          preview={data?.thumbnailImage}
          errorText={errors.thumbnailImage?.message || serverErrors.thumbnail}
          description={t("input svg thumbnail description")}
          accept="image/svg+xml"
          {...register("thumbnailImage", {
            validate: {
              size: (v) => validateFilesSize(v),
              extension: (v) => validateFilesExtension(v, ["image/svg+xml"]),
            },
          })}
        />
        <Flex justify="right" mt={4} gap={4}>
          <Button colorScheme="gray" fontSize="sm" onClick={onClose}>
            {t("close")}
          </Button>
          <Button
            type="submit"
            fontSize="sm"
            isLoading={isSubmitting}
            loadingText={t("please wait")}
          >
            {t("submit")}
          </Button>
        </Flex>
      </Flex>
    </Modal>
  );
};

export default EditModal;
