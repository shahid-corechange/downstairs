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

import Addon from "@/types/addon";
import Category from "@/types/category";
import Product from "@/types/product";
import Service from "@/types/service";

import { getTranslatedOptions } from "@/utils/autocomplete";
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
  categoryIds: string;
  addonIds: string;
  productIds: string;
  type: string;
  membershipType: string;
  price: number;
  vatGroup: number;
  hasRut: number;
  thumbnailImage?: FileList;
  thumbnail?: File;
};

export interface EditModalProps {
  isOpen: boolean;
  onClose: () => void;
  data?: Service;
  categories: Category[];
  addons: Addon[];
  products: Product[];
}

const EditModal = ({
  data,
  onClose,
  isOpen,
  categories,
  addons,
  products,
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
  const membershipTypeOptions = useConst(
    getTranslatedOptions(SERVICE_MEMBERSHIP_TYPES),
  );
  const typeOptions = useConst(getTranslatedOptions(SERVICE_TYPES));
  const rutOptions = useConst(getTranslatedOptions(BOOLEAN_OPTIONS));

  const handleSubmit = formSubmitHandler(
    ({ thumbnailImage, categoryIds, addonIds, productIds, ...values }) => {
      if (thumbnailImage && thumbnailImage.length > 0) {
        const file = thumbnailImage[0];
        values.thumbnail = file;
      }

      if (values.membershipType === ServiceMembershipType.COMPANY) {
        values.hasRut = 0;
      }

      const newValues = {
        ...values,
        categoryIds: JSON.parse(categoryIds),
        addonIds: addonIds ? JSON.parse(addonIds) : [],
        productIds: productIds ? JSON.parse(productIds) : [],
      };

      setIsSubmitting(true);
      router.post(
        `/services/${data?.id}`,
        { _method: "PATCH", ...newValues },
        {
          onFinish: () => setIsSubmitting(false),
          onSuccess: onClose,
        },
      );
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
    reset({
      name: {
        sv_SE: data?.translations?.sv_SE?.name?.value,
        en_US: data?.translations?.en_US?.name?.value,
      },
      description: {
        sv_SE: data?.translations?.sv_SE?.description?.value,
        en_US: data?.translations?.en_US?.description?.value,
      },
      categoryIds: JSON.stringify(
        (data?.categories || []).map((item) => item.id),
      ),
      addonIds: JSON.stringify((data?.addons || []).map((item) => item.id)),
      productIds: JSON.stringify((data?.products || []).map((item) => item.id)),
      membershipType: data?.membershipType,
      type: data?.type,
      price: data?.priceWithVat,
      vatGroup: data?.vatGroup,
      hasRut: Number(data?.hasRut),
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data]);

  return (
    <Modal title={t("edit service")} onClose={onClose} isOpen={isOpen}>
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
          value={watch("membershipType")}
          {...register("membershipType", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <Autocomplete
          options={typeOptions}
          labelText={t("type")}
          errorText={errors.type?.message || serverErrors.type}
          value={watch("type")}
          {...register("type", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <Input
          labelText={t("price per quarter")}
          helperText={t("price including vat")}
          type="number"
          min={0}
          errorText={errors.price?.message || serverErrors.price}
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
              value={watch("hasRut")}
              {...register("hasRut", {
                required: t("validation field required"),
              })}
              isRequired
            />
          )}
        </Flex>
        <FileInput
          labelText={t("thumbnail")}
          preview={data?.thumbnailImage}
          errorText={errors.thumbnailImage?.message}
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
            {t("save")}
          </Button>
        </Flex>
      </Flex>
    </Modal>
  );
};

export default EditModal;
