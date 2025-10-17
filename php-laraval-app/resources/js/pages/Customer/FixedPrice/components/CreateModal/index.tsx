import {
  Button,
  Flex,
  Icon,
  IconButton,
  Tooltip,
  useConst,
} from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { FieldError, useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";
import { RiCheckDoubleLine } from "react-icons/ri";

import Alert from "@/components/Alert";
import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import InputComposer from "@/components/InputComposer";
import ComposerTemplate from "@/components/InputComposer/ComposerTemplate";
import Modal from "@/components/Modal";

import { SIMPLE_TIME_FORMAT, WEEKDAYS } from "@/constants/datetime";
import {
  FIXED_PRICE_ROW_TYPES,
  FIXED_PRICE_TYPES,
} from "@/constants/fixedPrice";
import { FREQUENCIES } from "@/constants/frequency";
import { VATS } from "@/constants/vat";

import { useGetCustomers } from "@/services/customer";
import { useGetProducts } from "@/services/product";
import {
  getSubscriptionTypes,
  useGetSubscriptions,
} from "@/services/subscription";

import SubscriptionCleaningDetail from "@/types/subscriptionCleaningDetail";

import { toDayjs } from "@/utils/datetime";

import { PageProps } from "@/types";

type FormValues = {
  userId: number;
  type: (typeof FIXED_PRICE_TYPES)[number];
  period: "per booking" | "monthly";
  subscriptionIds: string;
  startDate?: string;
  endDate?: string;
  laundryProductIds: string;
  rows: {
    type: (typeof FIXED_PRICE_ROW_TYPES)[number];
    quantity: number;
    price: number;
    vatGroup: number;
  }[];
};

export interface CreateModalProps {
  isOpen: boolean;
  onClose: () => void;
}

const CreateModal = ({ isOpen, onClose }: CreateModalProps) => {
  const { t } = useTranslation();

  const { errors: serverErrors } = usePage<PageProps>().props;
  const {
    register,
    reset,
    watch,
    setValue,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    defaultValues: {
      period: "monthly",
    },
  });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const userId = watch("userId");
  const rows = watch("rows", []);
  const period = watch("period");
  const type = watch("type");

  const customers = useGetCustomers({
    request: {
      size: -1,
      show: "active",
      only: ["id", "fullname"],
    },
    query: {
      enabled: isOpen,
      staleTime: Infinity,
    },
  });

  const subscriptionTypes = useConst(getSubscriptionTypes(type));

  const subscriptions = useGetSubscriptions({
    request: {
      size: -1,
      filter: {
        eq: {
          userId,
          fixedPriceId: "null",
        },
        in: {
          subscribableType: subscriptionTypes,
        },
      },
      show: "active",
      include: [
        "service",
        "detail.team",
        "detail.property.address.city.country",
        "detail.pickupTeam",
        "detail.pickupProperty.address.city.country",
        "detail.deliveryTeam",
        "detail.deliveryProperty.address.city.country",
      ],
      only: [
        "id",
        "subscribableType",
        "subscribableId",
        "startTime",
        "endTime",
        "frequency",
        "weekday",
        "service.name",
        "detail.teamName",
        "detail.address",
      ],
    },
    query: {
      enabled: !!userId,
    },
  });

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

  const customerOptions = useMemo(
    () =>
      customers.data
        ? customers.data.map(({ id, fullname }) => ({
            label: fullname,
            value: id,
          }))
        : [],
    [customers.data],
  );

  const subscriptionOptions = useMemo(
    () =>
      subscriptions.data?.map(
        ({ id, service, frequency, weekday, startTime, endTime, detail }) => {
          const subscriptionDetail = detail as SubscriptionCleaningDetail;

          const fullAddress =
            subscriptionDetail?.property?.address?.fullAddress ?? "-";
          const serviceName = service?.name ?? "-";
          const teamName = subscriptionDetail?.team?.name ?? "-";
          const frequencyLabel = t(
            FREQUENCIES[frequency as keyof typeof FREQUENCIES],
          );
          const weekdayLabel = t(WEEKDAYS[weekday - 1]);
          const startTimeAt = toDayjs(startTime).format(SIMPLE_TIME_FORMAT);
          const endTimeAt = toDayjs(endTime).format(SIMPLE_TIME_FORMAT);

          return {
            label: [
              fullAddress,
              serviceName,
              teamName,
              frequencyLabel,
              weekdayLabel,
              `${startTimeAt}-${endTimeAt}`,
            ].join(", "),
            value: id,
          };
        },
      ) ?? [],
    [subscriptions.data],
  );

  const rowTypeOptions = useMemo(
    () =>
      FIXED_PRICE_ROW_TYPES.filter((value) => {
        if (type === "laundry" && value === "service") {
          return false;
        } else if (type === "cleaning" && value === "laundry") {
          return false;
        }
        return true;
      }).map((value) => ({
        label: t(value),
        value: value,
      })),
    [type],
  );

  const periodOptions = useConst([
    { label: t("per booking"), value: "per booking" },
    { label: t("monthly"), value: "monthly" },
  ]);

  const typeOptions = useMemo(
    () =>
      FIXED_PRICE_TYPES.map((value) => ({
        label: t(value),
        value: value.replaceAll(" ", "_"),
      })),
    [],
  );

  const isIncludeLaundry = useMemo(
    () => rows.find((row) => row.type === "laundry") !== undefined,
    [rows],
  );

  const selectedRowTypes = useMemo(() => rows.map((row) => row.type), [rows]);

  const handleSubmit = formSubmitHandler((values) => {
    const newValues = {
      ...values,
      subscriptionIds: values.subscriptionIds
        ? JSON.parse(values.subscriptionIds)
        : [],
      laundryProductIds:
        selectedRowTypes.includes("laundry") && values.laundryProductIds
          ? JSON.parse(values.laundryProductIds)
          : [],
      rows: values.rows.map((row) => ({
        ...row,
        quantity: 1,
      })),
      isPerOrder: values.period === "per booking",
    };

    setIsSubmitting(true);
    router.post("/customers/fixedprices", newValues, {
      onFinish: () => {
        setIsSubmitting(false);
      },
      onSuccess: (page) => {
        const {
          flash: { error },
        } = (page as Page<PageProps>).props;

        if (error) {
          return;
        }

        onClose();
      },
    });
  });

  const selectAllSubscriptions = () => {
    const allOptions = subscriptionOptions.map((option) => option.value);
    const stringValue = JSON.stringify(allOptions);
    setValue("subscriptionIds", stringValue);
  };

  useEffect(() => {
    if (userId) {
      setValue("subscriptionIds", "");
    }
  }, [userId]);

  useEffect(() => {
    if (isOpen) {
      reset();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen]);

  return (
    <Modal
      title={t("create fixed price")}
      size="5xl"
      isOpen={isOpen}
      onClose={onClose}
    >
      <Alert
        status="info"
        title={t("info")}
        message={
          t("create fixed prices info") +
          "\n" +
          t("default fixed price info") +
          "\n" +
          t("fixed price per order info") +
          "\n" +
          t("fixed price laundry info")
        }
        fontSize="small"
        mb={6}
      />
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
        <Autocomplete
          options={customerOptions}
          labelText={t("customer")}
          errorText={errors.userId?.message || serverErrors.userId}
          value={userId}
          {...register("userId", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <Autocomplete
          options={typeOptions}
          labelText={t("type")}
          errorText={errors.type?.message || serverErrors.type}
          value={type}
          {...register("type", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <Autocomplete
          options={periodOptions}
          labelText={t("period")}
          errorText={errors.period?.message || serverErrors.isPerOrder}
          value={period}
          {...register("period", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <Flex gap={4}>
          <Input
            type="date"
            labelText={t("date start")}
            errorText={errors.startDate?.message || serverErrors.startDate}
            {...register("startDate")}
          />
          <Input
            type="date"
            labelText={t("date end")}
            errorText={errors.endDate?.message || serverErrors.endDate}
            {...register("endDate")}
          />
        </Flex>
        <Autocomplete
          labelText={t("subscriptions")}
          options={subscriptionOptions}
          errorText={
            errors.subscriptionIds?.message || serverErrors.subscriptionIds
          }
          value={watch("subscriptionIds")}
          {...register("subscriptionIds", {
            required:
              type !== "laundry" ? t("validation field required") : false,
          })}
          label={{
            mt: "auto",
            mb: 0,
          }}
          labelContainer={{
            mb: 2,
          }}
          labelEnd={
            <Tooltip label={t("select all subscriptions")}>
              <IconButton
                variant="outline"
                aria-label={t("select all subscriptions")}
                size="xs"
                isRound
                onClick={selectAllSubscriptions}
              >
                <Icon as={RiCheckDoubleLine} boxSize={4} />
              </IconButton>
            </Tooltip>
          }
          maxTags={2}
          multiple
          isRequired={type !== "laundry"}
        />
        <InputComposer
          rowProps={{
            buttonProps: {
              mt: "26px",
            },
          }}
          onChange={(rows) => setValue("rows", rows as FormValues["rows"])}
        >
          <ComposerTemplate name="type" options={rowTypeOptions} unique>
            {({ index, ...props }) => (
              <Autocomplete
                size="xs"
                labelText={t("type")}
                errorText={
                  (errors.rows?.[index]?.type as FieldError | undefined)
                    ?.message || serverErrors[`rows.${index}.type`]
                }
                {...register(`rows.${index}.type`, {
                  required: t("validation field required"),
                })}
                {...props}
                isRequired
              />
            )}
          </ComposerTemplate>
          <ComposerTemplate name="price" defaultValue={0}>
            {({ index, value, onChange }) => (
              <Input
                type="number"
                size="xs"
                labelText={t("price")}
                helperText={t("price including vat")}
                errorText={
                  errors.rows?.[index]?.price?.message ||
                  serverErrors[`rows.${index}.price`]
                }
                min={0}
                value={value}
                {...register(`rows.${index}.price`, {
                  required: t("validation field required"),
                  valueAsNumber: true,
                })}
                onChange={onChange}
                isRequired
              />
            )}
          </ComposerTemplate>
          <ComposerTemplate name="vatGroup" options={VATS} defaultValue={25}>
            {({ index, ...props }) => (
              <Autocomplete
                size="xs"
                labelText={t("vat")}
                errorText={
                  errors.rows?.[index]?.vatGroup?.message ||
                  serverErrors[`rows.${index}.vatGroup`]
                }
                {...register(`rows.${index}.vatGroup`, {
                  required: t("validation field required"),
                  valueAsNumber: true,
                })}
                {...props}
                isRequired
              />
            )}
          </ComposerTemplate>
        </InputComposer>
        {isIncludeLaundry && (
          <Autocomplete
            labelText={t("laundry product")}
            options={productOptions}
            errorText={
              errors.laundryProductIds?.message ||
              serverErrors.laundryProductIds
            }
            value={watch("laundryProductIds")}
            {...register("laundryProductIds")}
            multiple
            maxTags={5}
          />
        )}
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
