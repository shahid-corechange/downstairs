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
import { DEFAULT_VAT, VATS } from "@/constants/vat";

import Addon from "@/types/addon";
import Category from "@/types/category";
import Service from "@/types/service";
import { Store } from "@/types/store";

import { getTranslatedOptions } from "@/utils/autocomplete";
import { getColor } from "@/utils/color";
import { validateFilesExtension, validateFilesSize } from "@/utils/validation";

import { PageProps } from "@/types";

interface FormValues {
  categoryIds: string;
  addonIds: string;
  serviceIds: string;
  storeIds: string;
  name: {
    sv_SE: string;
    en_US: string;
  };
  description: {
    sv_SE: string;
    en_US: string;
  };
  unit: string;
  price: number;
  creditPrice: number;
  vatGroup: number;
  hasRut: number;
  color: string;
  thumbnailImage?: FileList;
  thumbnail?: File;
}

export interface CreateModalProps {
  addons: Addon[];
  services: Service[];
  stores: Store[];
  categories: Category[];
  isOpen: boolean;
  onClose: () => void;
}

const CreateModal = ({
  addons,
  services,
  categories,
  stores,
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

  const storeIds = watch("storeIds");
  const serviceIds = watch("serviceIds");
  const hasCompanyService = useMemo(() => {
    if (!serviceIds) {
      return false;
    }

    const arrServiceIds = JSON.parse(serviceIds);
    return services.some(
      (service) =>
        service.membershipType === ServiceMembershipType.COMPANY &&
        arrServiceIds.includes(service.id),
    );
  }, [services, serviceIds]);

  const unitOptions = useConst(
    getTranslatedOptions(UNITS, (option) => ({
      label: `${t(option.label)} (${option.value})`,
      value: option.value,
    })),
  );

  const rutOptions = useConst(getTranslatedOptions(BOOLEAN_OPTIONS));
  const vatOptions = useConst(VATS);

  const handleSubmit = formSubmitHandler(
    ({
      thumbnailImage,
      hasRut,
      addonIds,
      serviceIds,
      categoryIds,
      ...values
    }) => {
      const newValues = {
        ...values,
        categoryIds: JSON.parse(categoryIds),
        addonIds: addonIds ? JSON.parse(addonIds) : [],
        serviceIds: serviceIds ? JSON.parse(serviceIds) : [],
        hasRut: hasCompanyService ? false : !!hasRut,
        storeIds: storeIds ? JSON.parse(storeIds) : [],
      };

      if (thumbnailImage && thumbnailImage.length > 0) {
        const file = thumbnailImage[0];
        newValues.thumbnail = file;
      }

      setIsSubmitting(true);
      router.post("/products", newValues, {
        onFinish: () => {
          setIsSubmitting(false);
        },
        onSuccess: () => {
          onClose();
        },
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

  const serviceOptions = useMemo(
    () =>
      services.map((item) => ({
        label: item.name,
        value: item.id,
      })),
    [services],
  );

  const storeOptions = useMemo(
    () =>
      stores.map((item) => ({
        label: item.name,
        value: item.id,
      })),
    [stores],
  );

  useEffect(() => {
    if (isOpen) {
      reset();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen]);

  return (
    <Modal title={t("create product")} isOpen={isOpen} onClose={onClose}>
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
          multiple
          isRequired
        />
        <Autocomplete
          options={addonOptions}
          labelText={`${t("addons")} (${t("addons visibility notice")})`}
          errorText={errors.addonIds?.message || serverErrors.addonIds}
          value={watch("addonIds")}
          {...register("addonIds")}
          multiple
        />
        <Autocomplete
          options={serviceOptions}
          labelText={t("services")}
          errorText={errors.serviceIds?.message || serverErrors.serviceIds}
          value={watch("serviceIds")}
          {...register("serviceIds")}
          multiple
        />
        <Autocomplete
          options={storeOptions}
          labelText={t("stores")}
          errorText={errors.storeIds?.message || serverErrors.storeIds}
          value={watch("storeIds")}
          {...register("storeIds")}
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
          <Autocomplete
            options={vatOptions}
            labelText={t("vat")}
            value={DEFAULT_VAT}
            errorText={errors.vatGroup?.message || serverErrors.vatGroup}
            {...register("vatGroup", {
              required: t("validation field required"),
              valueAsNumber: true,
            })}
            isRequired
          />
          {!hasCompanyService && (
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
