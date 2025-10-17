import {
  Button,
  Checkbox,
  Flex,
  Spinner,
  Text,
  Textarea,
} from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Input from "@/components/Input";
import Modal from "@/components/Modal";

import { useGetScheduleWorker } from "@/services/schedule";

import Deviation from "@/types/deviation";

import { getQuarter, toDayjs } from "@/utils/datetime";

type FormValues = {
  startAt: string;
  endAt: string;
  quarters: number;
  reason: string;
};

export interface EditAttendanceModalProps {
  isOpen: boolean;
  onClose: () => void;
  data?: Deviation;
}

const EditAttendanceModal = ({
  data,
  onClose,
  isOpen,
}: EditAttendanceModalProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;
  const {
    register,
    watch,
    reset,
    setValue,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const startAt = watch("startAt");
  const endAt = watch("endAt");
  const quarters = watch("quarters");
  const reason = watch("reason");

  const minQuarters = -1 * getQuarter(toDayjs(startAt), toDayjs(endAt));

  const isTimeAdjustmentOptional = (!quarters || quarters === 0) && !reason;

  const scheduleEmployee = useGetScheduleWorker(
    data?.schedule?.id ?? 0,
    data?.user?.id ?? 0,
    {
      request: {
        only: ["startAt", "endAt"],
      },
    },
  );

  const formattedScheduleStartAt = toDayjs(data?.schedule?.startAt).format(
    "YYYY-MM-DDTHH:mm",
  );
  const formattedScheduleEndAt = toDayjs(data?.schedule?.endAt).format(
    "YYYY-MM-DDTHH:mm",
  );

  const handleUseScheduleTime = () => {
    setValue(
      "startAt",
      toDayjs(data?.schedule?.startAt).format("YYYY-MM-DDTHH:mm"),
    );
    setValue(
      "endAt",
      toDayjs(data?.schedule?.endAt).format("YYYY-MM-DDTHH:mm"),
    );
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
      `/deviations/employee/${data?.id}/attendance`,
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
        onSuccess: onClose,
      },
    );
  });

  useEffect(() => {
    reset({
      startAt: toDayjs(
        scheduleEmployee.data?.startAt ??
          data?.schedule?.actualStartAt ??
          data?.schedule?.startAt ??
          undefined,
      ).format("YYYY-MM-DDTHH:mm"),
      endAt: toDayjs(
        scheduleEmployee.data?.endAt ??
          data?.schedule?.actualEndAt ??
          data?.schedule?.endAt ??
          undefined,
      ).format("YYYY-MM-DDTHH:mm"),
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data, scheduleEmployee.data]);

  return (
    <Modal title={t("edit attendance")} onClose={onClose} isOpen={isOpen}>
      {!scheduleEmployee.isFetching ? (
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
            labelText={t("attendance start")}
            errorText={errors.startAt?.message || serverErrors.startAt}
            {...register("startAt", {
              required: t("validation field required"),
            })}
            isRequired
          />
          <Input
            type="datetime-local"
            labelText={t("attendance end")}
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
                errors.quarters?.message ||
                serverErrors["time_adjustment.quarters"]
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
      ) : (
        <Flex h="xs" alignItems="center" justifyContent="center">
          <Spinner size="md" />
        </Flex>
      )}
    </Modal>
  );
};

export default EditAttendanceModal;
