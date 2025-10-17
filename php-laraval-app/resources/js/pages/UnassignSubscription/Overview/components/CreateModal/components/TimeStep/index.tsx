import { Flex, useConst } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useEffect, useMemo } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import TimePicker from "@/components/TimePicker";
import { useWizard } from "@/components/Wizard/hooks";

import { DATE_FORMAT, SIMPLE_TIME_FORMAT } from "@/constants/datetime";

import { toDayjs } from "@/utils/datetime";

import { PageProps } from "@/types";

import {
  StepsValues,
  TimeFormValues,
  UnassignSubscriptionPageProps,
} from "../../../../types";

const TimeStep = () => {
  const { t } = useTranslation();

  const {
    frequencies,
    query,
    errors: serverErrors,
  } = usePage<PageProps<UnassignSubscriptionPageProps>>().props;

  const {
    stepsValues,
    isValidating,
    moveTo,
    onValidateSuccess,
    onValidateError,
  } = useWizard<StepsValues, TimeFormValues>();

  const {
    register,
    watch,
    setValue,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<TimeFormValues>({
    defaultValues: {
      ...stepsValues[1],
      isFixed: stepsValues[1].isFixed ?? "false",
      frequency: query?.startAt ? 0 : stepsValues[1].frequency ?? 1,
      startAt: query?.startAt
        ? toDayjs(query.startAt).format(DATE_FORMAT)
        : stepsValues[1].startAt,
      endAt: query?.startAt
        ? toDayjs(query.startAt).format(DATE_FORMAT)
        : stepsValues[1].endAt,
      startTimeAt: query?.startAt
        ? toDayjs(query.startAt).format(SIMPLE_TIME_FORMAT)
        : stepsValues[1].startTimeAt,
    },
  });

  const frequency = watch("frequency");
  const startAt = watch("startAt");
  const startTimeAt = watch("startTimeAt");

  const fixOptions = useConst([
    { label: t("yes"), value: "true" },
    { label: t("no"), value: "false" },
  ]);

  const frequencyOptions = useMemo(
    () =>
      Object.entries(frequencies).map(([key, value]) => ({
        label: value,
        value: Number(key),
      })),
    [frequencies],
  );

  const handleSubmit = formSubmitHandler(onValidateSuccess, onValidateError);

  useEffect(() => {
    const { quarters } = stepsValues[0];

    if (!quarters || !startAt || !startTimeAt) {
      return;
    }

    const dayjsStartAt = toDayjs(`${startAt} ${startTimeAt}:00`, false);
    setValue("utcStartAt", dayjsStartAt.utc());
  }, [stepsValues[0], frequency, startAt, startTimeAt, setValue]);

  useEffect(() => {
    if (isValidating) {
      handleSubmit();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isValidating]);

  return (
    <Flex
      as="form"
      w="full"
      direction="column"
      gap={4}
      onSubmit={(e) => {
        e.preventDefault();
        moveTo("next");
      }}
    >
      <Autocomplete
        options={fixOptions}
        labelText={t("fixed time")}
        errorText={errors.isFixed?.message || serverErrors.isFixed}
        value={watch("isFixed")}
        {...register("isFixed", {
          required: t("validation field required"),
        })}
        isRequired
      />
      <Flex gap={4}>
        <Autocomplete
          options={frequencyOptions}
          labelText={t("frequency")}
          errorText={errors.frequency?.message || serverErrors.frequency}
          value={watch("frequency")}
          {...register("frequency", {
            required: t("validation field required"),
            valueAsNumber: true,
            onChange: () => setValue("endAt", ""),
          })}
          isRequired
        />
      </Flex>
      <Flex gap={4}>
        <Input
          type="date"
          labelText={t("date start")}
          helperText={t("unassign subscription date start helper text")}
          errorText={errors.startAt?.message || serverErrors.startAt}
          {...register("startAt", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <Input
          type="date"
          labelText={t("date end")}
          helperText={t("unassign subscription date end helper text")}
          errorText={errors.endAt?.message || serverErrors.endAt}
          {...register("endAt")}
          isReadOnly={frequency === 0}
        />
      </Flex>
      <Flex gap={4}>
        <TimePicker
          labelText={t("time start")}
          helperText={t("unassign subscription time start helper text")}
          errorText={errors.startTimeAt?.message || serverErrors.startTimeAt}
          value={startTimeAt}
          {...register("startTimeAt", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <Input
          labelText={t("time end")}
          helperText={t("unassign subscription time end helper text")}
          {...register("endTimeAt")}
          isReadOnly
        />
      </Flex>
    </Flex>
  );
};

export default TimeStep;
