import { Button, Flex, Textarea, useConst } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { FieldError, useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import InputComposer from "@/components/InputComposer";
import ComposerTemplate from "@/components/InputComposer/ComposerTemplate";
import { InputComposerState } from "@/components/InputComposer/types";
import Modal from "@/components/Modal";

import { useGetAddons } from "@/services/addon";
import { useGetAllFixedPrices } from "@/services/fixedPrice";
import { useGetProducts } from "@/services/product";
import { useGetServices } from "@/services/service";

import { PageProps } from "@/types";

interface FormValues {
  type: string;
  executionDate: string;
  priceType: string;
  price: number;
  description: string;
  rows: {
    id: string;
  }[];
}

export interface CreateModalProps {
  isOpen: boolean;
  onClose: () => void;
}

const CreateModal = ({ onClose, isOpen }: CreateModalProps) => {
  const { t } = useTranslation();

  const { errors: serverErrors } = usePage<PageProps>().props;
  const {
    register,
    reset,
    setValue,
    watch,
    resetField,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    defaultValues: {
      type: "service",
      priceType: "fixed_price",
    },
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const composerRef = useRef<InputComposerState>(null);

  const type = watch("type");
  const priceType = watch("priceType");
  const price = watch("price");

  const services = useGetServices({
    request: {
      size: -1,
      show: "active",
      only: ["id", "name", "priceWithVat"],
    },
    query: {
      staleTime: Infinity,
    },
  });

  const addons = useGetAddons({
    request: {
      size: -1,
      show: "active",
      only: ["id", "name", "priceWithVat"],
    },
    query: {
      staleTime: Infinity,
    },
  });

  const products = useGetProducts({
    request: {
      size: -1,
      show: "active",
      only: ["id", "name", "priceWithVat"],
    },
    query: {
      staleTime: Infinity,
    },
  });

  const fixedPrices = useGetAllFixedPrices({
    request: {
      size: -1,
      show: "active",
      include: ["user", "rows"],
      only: [
        "id",
        "user.fullname",
        "rows.type",
        "rows.priceWithVat",
        "startDate",
        "endDate",
        "isPerOrder",
      ],
    },
    query: {
      staleTime: Infinity,
    },
  });

  const handleSubmit = formSubmitHandler(({ rows, ...values }) => {
    setIsSubmitting(true);

    const newValues = {
      ...values,
      rowIds: rows.map((row) => row.id),
    };

    router.post("/price-adjustments", newValues, {
      onFinish: () => {
        setIsSubmitting(false);
      },
      onSuccess: () => {
        onClose();
      },
    });
  });

  const typeOptions = useConst([
    { value: "service", label: t("service") },
    { value: "addon", label: t("addon") },
    { value: "product", label: t("product") },
    { value: "fixed_price", label: t("fixed price") },
  ]);

  const rowOptions = useMemo(() => {
    switch (type) {
      case "service":
        return (
          services.data?.map((item) => ({
            value: String(item.id),
            label: item.name,
          })) ?? []
        );
      case "addon":
        return (
          addons.data?.map((item) => ({
            value: String(item.id),
            label: item.name,
          })) ?? []
        );
      case "product":
        return (
          products.data?.map((item) => ({
            value: String(item.id),
            label: item.name,
          })) ?? []
        );
      case "fixed_price":
        return (
          fixedPrices.data?.map((item) => ({
            value: String(item.id),
            label: `#${item.id} - ${item?.user?.fullname ?? ""} - ${
              item.isPerOrder ? t("per booking") : t("monthly")
            }`,
          })) ?? []
        );
      default:
        return [];
    }
  }, [type, services.data, addons.data, products.data, fixedPrices.data]);

  const priceTypeOptions = useMemo(
    () => {
      switch (type) {
        case "fixed_price":
          return [
            { value: "dynamic_percentage", label: t("dynamic percentage") },
          ];
        default:
          return [
            { value: "fixed_price_with_vat", label: t("fixed price") },
            { value: "dynamic_percentage", label: t("dynamic percentage") },
            { value: "dynamic_fixed_with_vat", label: t("dynamic fixed") },
          ];
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [type],
  );

  const PriceField = useCallback(() => {
    switch (priceType) {
      case "dynamic_percentage":
        return (
          <Input
            labelText={t("price")}
            helperText={t("price including vat")}
            suffix="%"
            type="number"
            max={100}
            errorText={errors.price?.message || serverErrors.price}
            {...register("price", {
              valueAsNumber: true,
              required: t("validation field required"),
            })}
            isRequired
          />
        );
      case "dynamic_fixed_with_vat":
        return (
          <Input
            labelText={t("price")}
            helperText={t("price including vat")}
            suffix="kr"
            type="number"
            errorText={errors.price?.message || serverErrors.price}
            {...register("price", {
              valueAsNumber: true,
              required: t("validation field required"),
            })}
            isRequired
          />
        );
      default:
        return (
          <Input
            labelText={t("price")}
            helperText={t("price including vat")}
            suffix="kr"
            type="number"
            min={0}
            errorText={errors.price?.message || serverErrors.price}
            {...register("price", {
              valueAsNumber: true,
              required: t("validation field required"),
            })}
            isRequired
          />
        );
    }
  }, [priceType, errors.price, serverErrors.price]);

  const productPrices = useMemo(() => {
    switch (type) {
      case "service":
        return services.data?.reduce<Record<string, number>>((acc, item) => {
          acc[`${item.id}`] = item.priceWithVat;
          return acc;
        }, {});
      case "addon":
        return addons.data?.reduce<Record<string, number>>((acc, item) => {
          acc[`${item.id}`] = item.priceWithVat;
          return acc;
        }, {});
      case "product":
        return products.data?.reduce<Record<string, number>>((acc, item) => {
          acc[`${item.id}`] = item.priceWithVat;
          return acc;
        }, {});
      case "fixed_price":
        return fixedPrices.data?.reduce<Record<string, number>>((acc, item) => {
          const serviceRow = item.rows?.find((row) => row.type === "service");

          if (serviceRow) {
            acc[`${item.id}`] = serviceRow.priceWithVat;
          }

          return acc;
        }, {});
      default:
        return {};
    }
  }, [type, services.data, addons.data, products.data, fixedPrices.data]);

  const getNewProductPrice = useCallback(
    (id: number) => {
      const previousPrice = productPrices?.[id] ?? 0;

      switch (priceType) {
        case "dynamic_fixed_with_vat":
          return previousPrice + price;
        case "dynamic_percentage":
          return previousPrice + (previousPrice * price) / 100;
        case "fixed_price_with_vat":
          return price;
        default:
          return 0;
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [type, priceType, price, productPrices],
  );

  useEffect(() => {
    if (isOpen) {
      reset();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen]);

  return (
    <Modal
      title={t("create price adjustment")}
      isOpen={isOpen}
      onClose={onClose}
    >
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
        {type === "fixed_price" && (
          <Alert
            status="info"
            title={t("info")}
            message={t("price adjustment for fixed price")}
            fontSize="small"
          />
        )}

        <Autocomplete
          options={typeOptions}
          labelText={t("type")}
          errorText={errors.type?.message || serverErrors.type}
          value={type}
          {...register("type", {
            onChange: () => {
              resetField("rows");
              composerRef.current?.reset();
            },
            required: t("validation field required"),
          })}
          isRequired
        />
        <Flex gap={4}>
          <Autocomplete
            options={priceTypeOptions}
            labelText={t("price type")}
            errorText={errors.priceType?.message || serverErrors.priceType}
            value={priceType}
            {...register("priceType", {
              onChange: () => {
                resetField("price");
              },
              required: t("validation field required"),
            })}
            isRequired
          />
          <PriceField />
        </Flex>

        <InputComposer
          ref={composerRef}
          rowProps={{
            buttonProps: {
              mt: "26px",
            },
          }}
          onChange={(rows) => setValue("rows", rows as FormValues["rows"])}
        >
          <ComposerTemplate name="id" options={rowOptions} unique>
            {({ index, onChange, ...props }) => (
              <Autocomplete
                {...props}
                size="xs"
                labelText={type ? t(type.replace("_", " ")) : " "}
                errorText={
                  (errors.rows?.[index]?.id as FieldError | undefined)
                    ?.message || serverErrors[`rows.${index}.id`]
                }
                {...register(`rows.${index}.id`, {
                  required: t("validation field required"),
                })}
                onChange={onChange}
                isRequired
              />
            )}
          </ComposerTemplate>
          <ComposerTemplate name="oldPrice">
            {({ index, rows }) => (
              <Input
                type="number"
                labelText={t("old price")}
                size="xs"
                value={
                  rows[index]?.id
                    ? productPrices?.[rows[index]?.id as string]
                    : ""
                }
                isReadOnly
              />
            )}
          </ComposerTemplate>
          <ComposerTemplate name="newPrice">
            {({ index, rows }) => (
              <Input
                size="xs"
                type="number"
                labelText={t("new price")}
                helperText={t("price including vat")}
                value={
                  rows[index]?.id
                    ? getNewProductPrice(rows[index].id as number)
                    : ""
                }
                isReadOnly
              />
            )}
          </ComposerTemplate>
        </InputComposer>

        <Input
          labelText={t("execution date")}
          type="date"
          errorText={
            errors.executionDate?.message || serverErrors.executionDate
          }
          {...register("executionDate", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <Input
          as={Textarea}
          labelText={t("description")}
          errorText={errors.description?.message || serverErrors.description}
          {...register("description")}
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
