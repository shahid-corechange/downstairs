import { Button, Flex } from "@chakra-ui/react";
import { useMemo } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";

import { useGetAvailableWorkers } from "@/services/schedule";

import ScheduleEmployee from "@/types/scheduleEmployee";

type FormValues = {
  userId: number;
};

interface BulkChangeFormProps {
  selectedScheduleEmployees: ScheduleEmployee[];
  onSubmit: (userId: number) => void;
  onClose: () => void;
}

const BulkChangeForm = ({
  selectedScheduleEmployees,
  onSubmit,
  onClose,
}: BulkChangeFormProps) => {
  const { t } = useTranslation();

  const { earliestStartAt, latestEndAt } = useMemo(() => {
    if (!selectedScheduleEmployees) {
      return {
        earliestStartAt: "",
        latestEndAt: "",
      };
    }

    const earliestStartAt =
      selectedScheduleEmployees.reduce((prev, current) => {
        if (!prev?.schedule?.startAt || !current?.schedule?.startAt) {
          return prev;
        }

        return prev.schedule.startAt < current.schedule.startAt
          ? prev
          : current;
      }, selectedScheduleEmployees[0]!)?.schedule?.startAt ?? "";

    const latestEndAt =
      selectedScheduleEmployees.reduce((prev, current) => {
        if (!prev?.schedule?.endAt || !current?.schedule?.endAt) {
          return prev;
        }

        return prev.schedule.endAt > current.schedule.endAt ? prev : current;
      }, selectedScheduleEmployees[0]!)?.schedule?.endAt ?? "";

    return { earliestStartAt, latestEndAt };
  }, [selectedScheduleEmployees]);

  const availableWorkers = useGetAvailableWorkers(
    earliestStartAt,
    latestEndAt,
    [],
    {
      request: {
        only: ["id", "fullname"],
      },
    },
  );

  const {
    register,
    watch,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();

  const workerOptions = useMemo(
    () =>
      availableWorkers.data?.map((item) => ({
        label: item.fullname,
        value: item.id,
      })) ?? [],
    [availableWorkers.data],
  );

  const handleSubmit = formSubmitHandler((values) => {
    onSubmit(values.userId);
    onClose();
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
        options={workerOptions}
        labelText={t("to")}
        errorText={errors.userId?.message}
        value={watch("userId")}
        {...register("userId", {
          required: t("validation field required"),
          valueAsNumber: true,
        })}
        isRequired
      />
      <Flex justify="right" mt={4} gap={4}>
        <Button colorScheme="gray" fontSize="sm" onClick={onClose}>
          {t("close")}
        </Button>
        <Button type="submit" fontSize="sm">
          {t("save")}
        </Button>
      </Flex>
    </Flex>
  );
};

export default BulkChangeForm;
