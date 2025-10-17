import { Button, Flex, Textarea } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { Dayjs } from "dayjs";
import { useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";

import { SIMPLE_TIME_FORMAT } from "@/constants/datetime";

import { useGetAllScheduleWorkers } from "@/services/schedule";

import { getQuarter, toDayjs } from "@/utils/datetime";

type FormValues = {
  scheduleEmployeeId: number;
  quarters: number;
  reason: string;
};

interface AddFormProps {
  scheduleIds?: number[];
  userId?: number;
  workHourId?: number;
  onClose: () => void;
  onRefetch: () => void;
}

const AddForm = ({
  scheduleIds,
  userId,
  workHourId,
  onClose,
  onRefetch,
}: AddFormProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;
  const {
    register,
    watch,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [startAt, setStartAt] = useState<Dayjs>();
  const [endAt, setEndAt] = useState<Dayjs>();

  const schedules = useGetAllScheduleWorkers(0, {
    request: {
      include: ["schedule.property.address"],
      only: ["id", "startAt", "endAt", "schedule.property.address.fullAddress"],
      filter: {
        eq: {
          status: "done",
          userId,
          workHourId,
        },
        notIn: {
          id: scheduleIds,
        },
      },
    },
    query: {
      enabled: !!scheduleIds && !!userId && !!workHourId,
    },
  });

  const scheduleOptions = useMemo(
    () =>
      schedules.data
        ? schedules.data.map(({ id, startAt, endAt, schedule }) => ({
            label: `${
              schedule?.property?.address?.fullAddress ?? ""
            }, ${toDayjs(startAt).format(SIMPLE_TIME_FORMAT)}-${toDayjs(
              endAt,
            ).format(SIMPLE_TIME_FORMAT)}`,
            value: id,
          }))
        : [],
    [schedules.data],
  );

  const minQuarters = -1 * getQuarter(startAt, endAt);

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);
    router.post(`/time-adjustments`, values, {
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
      <Autocomplete
        options={scheduleOptions}
        labelText={t("schedule")}
        errorText={
          errors.scheduleEmployeeId?.message || serverErrors.scheduleEmployeeId
        }
        value={watch("scheduleEmployeeId")}
        {...register("scheduleEmployeeId", {
          required: t("validation field required"),
          onChange: (e) => {
            const schedule = schedules.data?.find(
              (schedule) => schedule.id === Number(e.target.value),
            );

            if (schedule) {
              setStartAt(toDayjs(schedule.startAt));
              setEndAt(toDayjs(schedule.endAt));
            }
          },
        })}
        isRequired
      />
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

export default AddForm;
