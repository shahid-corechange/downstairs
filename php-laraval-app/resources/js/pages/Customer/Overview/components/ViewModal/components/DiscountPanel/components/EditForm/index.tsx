import { Button, Checkbox, Flex, Text, useConst } from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { router, usePage } from "@inertiajs/react";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";

import { DATE_FORMAT } from "@/constants/datetime";
import { DISCOUNT_TYPES } from "@/constants/discount";

import CustomerDiscount from "@/types/customerDiscount";
import User from "@/types/user";

import { getTranslatedOptions } from "@/utils/autocomplete";
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

export interface EditFormProps {
  user: User;
  data: CustomerDiscount;
  onRefetch: () => void;
  onClose: () => void;
}

const EditForm = ({ user, data, onRefetch, onClose }: EditFormProps) => {
  const { t } = useTranslation();

  const { errors: serverErrors } = usePage().props;
  const {
    register,
    watch,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    defaultValues: {
      userId: user.id,
      type: data.type,
      value: data.value,
      usageLimit: data.usageLimit,
      startDate: data.startDate
        ? toDayjs(data.startDate).format(DATE_FORMAT)
        : undefined,
      endDate: data.endDate
        ? toDayjs(data.endDate).format(DATE_FORMAT)
        : undefined,
    },
  });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const [unlimitedUsageToggle, setUnlimitedUsageToggle] = useState(
    !data.usageLimit && data.usageLimit !== 0,
  );
  const [indefinitelyDateToggle, setIndefinetlyDateToggle] = useState(
    !data.startDate && !data.endDate,
  );

  const typeOptions = useConst(getTranslatedOptions(DISCOUNT_TYPES));

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);
    router.patch(
      `/customers/discounts/${data.id}`,
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

          onRefetch();
          onClose();
        },
      },
    );
  });

  return (
    <>
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
        fontSize="xs"
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
        <Input
          labelText={t("customer")}
          defaultValue={user.fullname}
          isReadOnly
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

        <Flex gap={4}>
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
        </Flex>

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
    </>
  );
};

export default EditForm;
