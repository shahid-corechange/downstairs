import { Button, Flex, Textarea } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Input from "@/components/Input";

import TimeAdjustment from "@/types/timeAdjustment";

import { getQuarter, toDayjs } from "@/utils/datetime";

type FormValues = {
  quarters: number;
  reason: string;
};

interface EditFormProps {
  timeAdjustment: TimeAdjustment;
  onClose: () => void;
  onRefetch: () => void;
}

const EditForm = ({ timeAdjustment, onClose, onRefetch }: EditFormProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;
  const {
    register,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    defaultValues: {
      quarters: timeAdjustment.quarters,
      reason: timeAdjustment.reason,
    },
  });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const startAt = toDayjs(timeAdjustment.schedule?.startAt ?? "");
  const endAt = toDayjs(timeAdjustment.schedule?.endAt ?? "");
  const minQuarters = -1 * getQuarter(startAt, endAt);

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);
    router.put(`/time-adjustments/${timeAdjustment.id}`, values, {
      onFinish: () => setIsSubmitting(false),
      onSuccess: () => {
        onClose();
        onRefetch();
      },
    });
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
      <Input
        labelText={t("quarters")}
        helperText={t("quarters helper text")}
        errorText={errors.quarters?.message || serverErrors.quarters}
        type="number"
        {...register("quarters", {
          required: t("validation field required"),
          min: {
            value: minQuarters,
            message: t("validation field min", { min: minQuarters }),
          },
        })}
        isRequired
      />
      <Input
        as={Textarea}
        labelText={t("reason")}
        errorText={errors.reason?.message || serverErrors.reason}
        resize="none"
        {...register("reason", {
          required: t("validation field required"),
        })}
        isRequired
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
  );
};

export default EditForm;
