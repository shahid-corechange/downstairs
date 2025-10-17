import { Button, Flex, Text, Textarea, useConst } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import FileInput from "@/components/FileInput";
import Input from "@/components/Input";
import Modal from "@/components/Modal";

import { BOOLEAN_OPTIONS } from "@/constants/boolean";
import {
  SERVICE_MEMBERSHIP_TYPES,
  SERVICE_TYPES,
  ServiceMembershipType,
} from "@/constants/service";
import { DEFAULT_VAT } from "@/constants/vat";

import Addon from "@/types/addon";
import Category from "@/types/category";
import Product from "@/types/product";

import { getTranslatedOptions } from "@/utils/autocomplete";
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
  categoryIds: string;
  addonIds: string;
  productIds: string;
  type: string;
  membershipType: string;
  price: number;
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
  thumbnailImage?: FileList;
  thumbnail?: File;
}

export interface CreateModalProps {
  categories: Category[];
  addons: Addon[];
  products: Product[];
  isOpen: boolean;
  onClose: () => void;
}

const CreateModal = ({
  onClose,
  isOpen,
  categories,
  addons,
  products,
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
      vatGroup: DEFAULT_VAT,
    },
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const membershipTypeOptions = useConst(
    getTranslatedOptions(SERVICE_MEMBERSHIP_TYPES),
  );
  const typeOptions = useConst(getTranslatedOptions(SERVICE_TYPES));
  const rutOptions = useConst(getTranslatedOptions(BOOLEAN_OPTIONS));

  const handleSubmit = formSubmitHandler(
    ({
      categoryIds,
      addonIds,
      productIds,
      thumbnailImage,
      task,
      ...values
    }) => {
      const newValues = {
        ...values,
        categoryIds: JSON.parse(categoryIds),
        addonIds: addonIds ? JSON.parse(addonIds) : [],
        productIds: productIds ? JSON.parse(productIds) : [],
        hasRut:
          values.membershipType === ServiceMembershipType.COMPANY
            ? 0
            : values.hasRut,
        tasks: [task],
        thumbnail:
          thumbnailImage && thumbnailImage.length > 0
            ? thumbnailImage[0]
            : undefined,
      };

      setIsSubmitting(true);
      router.post("/services", newValues, {
        onFinish: () => setIsSubmitting(false),
        onSuccess: onClose,
      });
    },
  );

  const categoryOptions = useMemo(
    () =>
      categories.map((item) => ({
        label: item.name,
        value: item.id,
      })),
    [categories],
  );

  const addonOptions = useMemo(
    () =>
      addons.map((item) => ({
        label: item.name,
        value: item.id,
      })),
    [addons],
  );

  const productOptions = useMemo(
    () =>
      products.map((item) => ({
        label: item.name,
        value: item.id,
      })),
    [products],
  );

  useEffect(() => {
    if (isOpen) {
      reset();
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen]);

  return (
    <Modal title={t("create service")} isOpen={isOpen} onClose={onClose}>
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
          options={addonOptions}
          labelText={t("addons")}
          errorText={errors.addonIds?.message || serverErrors.addonIds}
          value={watch("addonIds")}
          {...register("addonIds")}
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
          options={membershipTypeOptions}
          labelText={t("membership type")}
          errorText={
            errors.membershipType?.message || serverErrors.membershipType
          }
          isRequired
          {...register("membershipType", {
            required: t("validation field required"),
          })}
        />
        <Autocomplete
          options={typeOptions}
          labelText={t("type")}
          errorText={errors.type?.message || serverErrors.type}
          isRequired
          {...register("type", {
            required: t("validation field required"),
          })}
        />
        <Input
          labelText={t("price per quarter")}
          helperText={t("price including vat")}
          type="number"
          min={0}
          errorText={errors.price?.message || serverErrors.price}
          resize="none"
          isRequired
          {...register("price", {
            required: t("validation field required"),
            valueAsNumber: true,
          })}
        />
        <Flex gap={4}>
          <Input
            labelText={t("vat")}
            defaultValue={watch("vatGroup")}
            isRequired
            isReadOnly
          />
          {watch("membershipType") === "private" && (
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
        <FileInput
          labelText={t("thumbnail")}
          errorText={errors.thumbnailImage?.message || serverErrors.thumbnail}
          accept="image/jpg, image/jpeg, image/png"
          {...register("thumbnailImage", {
            validate: {
              size: (v) => validateFilesSize(v),
              extension: (v) =>
                validateFilesExtension(v, [
                  "image/jpg",
                  "image/jpeg",
                  "image/png",
                ]),
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
