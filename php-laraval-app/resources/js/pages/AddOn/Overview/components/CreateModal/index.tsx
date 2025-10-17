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
import { DEFAULT_VAT } from "@/constants/vat";

import Category from "@/types/category";
import Product from "@/types/product";
import Service from "@/types/service";

import { getTranslatedOptions } from "@/utils/autocomplete";
import { getColor } from "@/utils/color";
import { validateFilesExtension, validateFilesSize } from "@/utils/validation";

import { PageProps } from "@/types";

interface FormValues {
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
  task: {
    name: {
      sv_SE: string;
      en_US: string;
    };
    description: {
      sv_SE: string;
      en_US: string;
    };
  };
  color: string;
  thumbnailImage?: FileList;
  thumbnail?: File;
}

export interface CreateModalProps {
  services: Service[];
  categories: Category[];
  products: Product[];
  isOpen: boolean;
  onClose: () => void;
}

const CreateModal = ({
  services,
  categories,
  products,
  onClose,
  isOpen,
}: CreateModalProps) => {
  const { t } = useTranslation();

  const { errors: serverErrors } = usePage<PageProps>().props;
  const {
    register,
    reset,
    watch,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    defaultValues: {
      color: getColor("gray.500"),
      vatGroup: DEFAULT_VAT,
    },
  });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const serviceIds = watch("serviceIds");
  const hasPrivateService = useMemo(() => {
    if (!serviceIds) {
      return false;
    }

    const arrServiceIds = JSON.parse(serviceIds);
    return services.some(
      (service) =>
        service.membershipType === ServiceMembershipType.PRIVATE &&
        arrServiceIds.includes(service.id),
    );
  }, [services, serviceIds]);

  const unitOptions = useConst(
    getTranslatedOptions(UNITS, (option) => ({
      label: `${t(option.label)} (${option.value})`,
      value: option.value,
    })),
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
      serviceIds,
      categoryIds,
      productIds,
      task,
      hasRut,
      ...values
    }) => {
      const newValues = {
        ...values,
        categoryIds: JSON.parse(categoryIds),
        serviceIds: JSON.parse(serviceIds),
        productIds: productIds ? JSON.parse(productIds) : [],
        tasks: [task],
        hasRut: hasPrivateService ? !!hasRut : false,
      };

      if (thumbnailImage && thumbnailImage.length > 0) {
        const file = thumbnailImage[0];
        newValues.thumbnail = file;
      }

      setIsSubmitting(true);
      router.post("/addons", newValues, {
        onFinish: () => {
          setIsSubmitting(false);
        },
        onSuccess: () => {
          onClose();
        },
      });
    },
  );

  const serviceOptions = useMemo(
    () =>
      services.map((item) => ({
        label: item.name,
        value: item.id,
      })),
    [services],
  );

  useEffect(() => {
    if (isOpen) {
      reset();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen]);

  return (
    <Modal title={t("create add on")} isOpen={isOpen} onClose={onClose}>
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
              {...register("hasRut", {
                required: t("validation field required"),
                valueAsNumber: true,
              })}
              isRequired
            />
          )}
        </Flex>
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
              labelText={t("task name")}
              errorText={
                errors.task?.name?.sv_SE?.message ||
                serverErrors["tasks.0.name.sv_SE"]
              }
              {...register("task.name.sv_SE", {
                required: t("validation field required"),
              })}
              isRequired
            />
            <Input
              as={Textarea}
              labelText={t("task description")}
              errorText={
                errors.task?.description?.sv_SE?.message ||
                serverErrors["tasks.0.description.sv_SE"]
              }
              resize="none"
              {...register("task.description.sv_SE", {
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
              labelText={t("task name")}
              errorText={
                errors.task?.name?.en_US?.message ||
                serverErrors["tasks.0.name.en_US"]
              }
              {...register("task.name.en_US", {
                required: t("validation field required"),
              })}
              isRequired
            />
            <Input
              as={Textarea}
              labelText={t("task description")}
              errorText={
                errors.task?.description?.en_US?.message ||
                serverErrors["tasks.0.description.en_US"]
              }
              resize="none"
              {...register("task.description.en_US", {
                required: t("validation field required"),
              })}
              isRequired
            />
          </Flex>
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
          errorText={errors.thumbnailImage?.message || serverErrors.thumbnail}
          previewContainer={{ bg: "gray.500" }}
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

export default CreateModal;
