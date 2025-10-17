import { Button, Checkbox, Flex, Text, Textarea } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Input from "@/components/Input";

import Schedule from "@/types/schedule";
import ScheduleEmployee from "@/types/scheduleEmployee";

import { getQuarter, toDayjs } from "@/utils/datetime";

type FormValues = {
  startAt: string;
  endAt: string;
  quarters: number;
  reason: string;
};

interface EditAttendanceFormProps {
  schedule?: Schedule;
  scheduleEmployee: ScheduleEmployee;
  onCancel: () => void;
  onRefetch: () => void;
}

const EditAttendanceForm = ({
  schedule,
  scheduleEmployee,
  onCancel,
  onRefetch,
}: EditAttendanceFormProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;
  const {
    register,
    watch,
    setValue,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    defaultValues: {
      startAt: toDayjs(
        scheduleEmployee.startAt ??
          schedule?.actualStartAt ??
          schedule?.startAt ??
          undefined,
      ).format("YYYY-MM-DDTHH:mm"),
      endAt: toDayjs(
        scheduleEmployee.endAt ??
          schedule?.actualEndAt ??
          schedule?.endAt ??
          undefined,
      ).format("YYYY-MM-DDTHH:mm"),
    },
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const startAt = watch("startAt");
  const endAt = watch("endAt");
  const quarters = watch("quarters");
  const reason = watch("reason");

  const minQuarters = -1 * getQuarter(toDayjs(startAt), toDayjs(endAt));

  const isTimeAdjustmentOptional = (!quarters || quarters === 0) && !reason;

  const formattedScheduleStartAt = toDayjs(schedule?.startAt).format(
    "YYYY-MM-DDTHH:mm",
  );
  const formattedScheduleEndAt = toDayjs(schedule?.endAt).format(
    "YYYY-MM-DDTHH:mm",
  );

  const handleUseScheduleTime = () => {
    setValue("startAt", toDayjs(schedule?.startAt).format("YYYY-MM-DDTHH:mm"));
    setValue("endAt", toDayjs(schedule?.endAt).format("YYYY-MM-DDTHH:mm"));
  };

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);

    const startAtUTC = toDayjs(
      toDayjs().format(`${values.startAt}:00Z`),
    ).toISOString();
    const endAtUTC = toDayjs(
      toDayjs().format(`${values.endAt}:00Z`),
    ).toISOString();

    router.patch(
      `/schedules/${schedule?.id}/workers/${scheduleEmployee?.user?.id}/attendance`,
      {
        startAt: startAtUTC,
        endAt: endAtUTC,
        timeAdjustment: !isTimeAdjustmentOptional
          ? {
              quarters: values.quarters,
              reason: values.reason,
            }
          : undefined,
      },
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
      <Input
        type="datetime-local"
        labelText={t("schedule start")}
        errorText={errors.startAt?.message || serverErrors.startAt}
        {...register("startAt", {
          required: t("validation field required"),
        })}
        isRequired
      />
      <Input
        type="datetime-local"
        labelText={t("schedule end")}
        errorText={errors.endAt?.message || serverErrors.endAt}
        {...register("endAt", {
          required: t("validation field required"),
        })}
        isRequired
      />
      <Flex>
        <Checkbox
          size="sm"
          onChange={({ currentTarget }) =>
            currentTarget.checked ? handleUseScheduleTime() : null
          }
          isChecked={
            startAt === formattedScheduleStartAt &&
            endAt === formattedScheduleEndAt
          }
        >
          {t("use schedule time")}
        </Checkbox>
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
          {t("time adjustment")}
        </Text>
        <Input
          labelText={t("quarters")}
          helperText={t("quarters helper text")}
          errorText={
            errors.quarters?.message || serverErrors["time_adjustment.quarters"]
          }
          type="number"
          {...register("quarters", {
            required:
              !isTimeAdjustmentOptional && t("validation field required"),
            min: {
              value: minQuarters,
              message: t("validation field min", { min: minQuarters }),
            },
          })}
          isRequired={!isTimeAdjustmentOptional}
        />
        <Input
          as={Textarea}
          labelText={t("reason")}
          errorText={
            errors.reason?.message || serverErrors["time_adjustment.reason"]
          }
          resize="none"
          {...register("reason", {
            required:
              !isTimeAdjustmentOptional && t("validation field required"),
          })}
          isRequired={!isTimeAdjustmentOptional}
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

export default EditAttendanceForm;
