import { Button, Flex } from "@chakra-ui/react";
import { useMemo } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";

import { useGetAvailableWorkers } from "@/services/schedule";

import ScheduleEmployee from "@/types/scheduleEmployee";

type FormValues = {
  userId: number;
};

interface ChangeFormProps {
  scheduleEmployee: ScheduleEmployee;
  onSubmit: (userId: number) => void;
  onClose: () => void;
}

const ChangeForm = ({
  scheduleEmployee,
  onSubmit,
  onClose,
}: ChangeFormProps) => {
  const { t } = useTranslation();

  const availableWorkers = useGetAvailableWorkers(
    scheduleEmployee?.schedule?.startAt ?? "",
    scheduleEmployee?.schedule?.endAt ?? "",
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
      <Input
        labelText={t("from")}
        defaultValue={scheduleEmployee?.user?.fullname}
        isRequired
        isDisabled
      />
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

export default ChangeForm;
