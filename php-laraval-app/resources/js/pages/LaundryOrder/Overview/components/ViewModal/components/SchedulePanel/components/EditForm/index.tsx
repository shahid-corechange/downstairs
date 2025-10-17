import { Button, Flex, useConst } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import TimePicker from "@/components/TimePicker";

import { DATE_FORMAT } from "@/constants/datetime";

import { LaundryOrderSchedule } from "@/types/laundryOrderSchedule";

import { getTranslatedOptions } from "@/utils/autocomplete";
import { toDayjs } from "@/utils/datetime";

const SCHEDULE_TYPES = [
  {
    label: "pickup",
    value: "pickup",
  },
  {
    label: "delivery",
    value: "delivery",
  },
];

type FormValues = {
  type: string;
  date: string;
  time: string;
};

interface EditFormProps {
  laundryOrderSchedule: LaundryOrderSchedule;
  laundryOrderId: number;
  onCancel: () => void;
  onRefetch: () => void;
}

const EditForm = ({
  laundryOrderSchedule,
  laundryOrderId,
  onCancel,
  onRefetch,
}: EditFormProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;
  const {
    watch,
    register,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    defaultValues: {
      type: laundryOrderSchedule.type,
      date: toDayjs(laundryOrderSchedule.date).format(DATE_FORMAT),
      time: laundryOrderSchedule.time,
    },
  });

  const [isSubmitting, setIsSubmitting] = useState(false);
  const typeOptions = useConst(getTranslatedOptions(SCHEDULE_TYPES));

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);

    router.patch(
      `/laundry-orders/${laundryOrderId}/schedules/${laundryOrderSchedule.id}`,
      values,
      {
        onFinish: () => setIsSubmitting(false),
        onSuccess: () => {
          onCancel();
          onRefetch();
        },
      },
    );
  });

  return (
    <Flex
      as="form"
      direction="column"
      gap={4}
      onSubmit={handleSubmit}
      autoComplete="off"
      noValidate
    >
      <Autocomplete
        options={typeOptions}
        labelText={t("type")}
        errorText={errors.type?.message || serverErrors.type}
        value={watch("type")}
        isRequired
        {...register("type", { required: t("validation field required") })}
      />
      <Flex direction="row" gap={4}>
        <Input
          type="date"
          labelText={t("date")}
          errorText={errors.date?.message || serverErrors.date}
          {...register("date", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <TimePicker
          labelText={t("time")}
          errorText={errors.time?.message || serverErrors.time}
          value={watch("time")}
          {...register("time", {
            required: t("validation field required"),
          })}
          isRequired
        />
      </Flex>
      <Flex justify="right" mt={4} gap={4}>
        <Button colorScheme="gray" fontSize="sm" onClick={onCancel}>
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
  );
};

export default EditForm;
