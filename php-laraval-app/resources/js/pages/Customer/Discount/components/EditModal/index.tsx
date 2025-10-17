import { Button, Checkbox, Flex, Text } from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import Modal from "@/components/Modal";

import { DATE_FORMAT } from "@/constants/datetime";

import { useGetCustomers } from "@/services/customer";

import CustomerDiscount, { DiscountType } from "@/types/customerDiscount";

import { toDayjs } from "@/utils/datetime";

import { PageProps } from "@/types";

type FormValues = {
  userId: number;
  type: string;
  value: number;
  usageLimit: number;
  startDate: string;
  endDate: string;
};

export interface EditModalProps {
  isOpen: boolean;
  onClose: () => void;
  data?: CustomerDiscount;
  discountType: DiscountType;
}

const EditModal = ({ data, discountType, onClose, isOpen }: EditModalProps) => {
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

  const [unlimitedUsageToggle, setUnlimitedUsageToggle] = useState(false);
  const [indefinitelyDateToggle, setIndefinetlyDateToggle] = useState(false);

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

  const typeOptions = useMemo(
    () =>
      Object.entries(discountType).map(([value, label]) => ({
        value,
        label: t(label),
      })),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [discountType],
  );

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);
    router.patch(
      `/customers/discounts/${data?.id}`,
      {
        ...values,
        usageLimit: unlimitedUsageToggle ? null : values.usageLimit,
        startDate: indefinitelyDateToggle ? null : values.startDate,
        endDate: indefinitelyDateToggle ? null : values.endDate,
      },
      {
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
      },
    );
  });

  useEffect(() => {
    if (isOpen && data) {
      reset({
        userId: data.userId,
        type: data.type,
        value: data.value,
        usageLimit: data.usageLimit,
        startDate: data.startDate
          ? toDayjs(data.startDate).format(DATE_FORMAT)
          : undefined,
        endDate: data.endDate
          ? toDayjs(data.endDate).format(DATE_FORMAT)
          : undefined,
      });
      setUnlimitedUsageToggle(!data.usageLimit);
      setIndefinetlyDateToggle(!data.startDate && !data.endDate);
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen, data]);

  return (
    <Modal
      title={t("edit customer discount")}
      onClose={onClose}
      isOpen={isOpen}
    >
      <Alert
        status="info"
        title={t("info")}
        message={
          t("input customer discount info") +
          "\n" +
          t("customer discount info") +
          "\n" +
          t("customer discount invoice info")
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
          value={watch("userId")}
          {...register("userId", {
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
          labelText={t("discount percentage")}
          errorText={errors.value?.message || serverErrors.value}
          type="number"
          {...register("value", {
            required: t("validation field required"),
          })}
          suffix="%"
          isRequired
        />

        <Input
          labelText={t("usage limit")}
          errorText={errors.usageLimit?.message || serverErrors.usageLimit}
          type="number"
          {...register("usageLimit", {
            required: !unlimitedUsageToggle && t("validation field required"),
          })}
          isRequired={!unlimitedUsageToggle}
          isReadOnly={unlimitedUsageToggle}
        />

        <Checkbox
          isChecked={unlimitedUsageToggle}
          onChange={(e) => setUnlimitedUsageToggle(e.target.checked)}
        >
          <Text fontSize="sm">{t("unlimited usage limit")}</Text>
        </Checkbox>

        <Flex gap={4}>
          <Input
            labelText={t("start date")}
            errorText={errors.startDate?.message || serverErrors.startDate}
            type="date"
            {...register("startDate", {
              required:
                !indefinitelyDateToggle && t("validation field required"),
            })}
            isRequired={!indefinitelyDateToggle}
            isReadOnly={indefinitelyDateToggle}
          />
          <Input
            labelText={t("end date")}
            errorText={errors.endDate?.message || serverErrors.endDate}
            type="date"
            {...register("endDate", {
              required:
                !indefinitelyDateToggle && t("validation field required"),
            })}
            isRequired={!indefinitelyDateToggle}
            isReadOnly={indefinitelyDateToggle}
          />
        </Flex>

        <Checkbox
          isChecked={indefinitelyDateToggle}
          onChange={(e) => setIndefinetlyDateToggle(e.target.checked)}
        >
          <Text fontSize="sm">{t("indefinitely discount")}</Text>
        </Checkbox>
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
