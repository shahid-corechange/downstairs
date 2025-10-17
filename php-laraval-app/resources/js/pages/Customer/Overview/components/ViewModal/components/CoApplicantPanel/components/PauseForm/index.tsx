import { Button, Flex } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import MonthPicker from "@/components/MonthPicker";

import { DATE_FORMAT } from "@/constants/datetime";

import { RutCoApplicant } from "@/types/rutCoApplicant";

import { toDayjs } from "@/utils/datetime";

type FormValues = {
  pauseStartDate: string;
  pauseEndDate: string;
};

interface PauseFormProps {
  userId: number;
  rutCoApplicant: RutCoApplicant;
  onCancel: () => void;
  onRefetch: () => void;
}

const PauseForm = ({
  userId,
  rutCoApplicant,
  onCancel,
  onRefetch,
}: PauseFormProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;
  const {
    register,
    watch,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    defaultValues: {
      pauseStartDate: rutCoApplicant.pauseStartDate || "",
      pauseEndDate: rutCoApplicant.pauseEndDate || "",
    },
  });

  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = formSubmitHandler(({ pauseStartDate, pauseEndDate }) => {
    setIsSubmitting(true);

    router.post(
      `/customers/${userId}/rut-co-applicants/${rutCoApplicant.id}/pause`,
      {
        pauseStartDate: pauseStartDate ? `${pauseStartDate}-01` : undefined,
        pauseEndDate: pauseEndDate
          ? toDayjs(pauseEndDate, false).endOf("month").format(DATE_FORMAT)
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
      <MonthPicker
        labelText={t("start month")}
        errorText={
          errors.pauseStartDate?.message || serverErrors.pauseStartDate
        }
        value={watch("pauseStartDate")}
        {...register("pauseStartDate", {
          required: t("validation field required"),
        })}
        isRequired
      />
      <MonthPicker
        labelText={t("end month")}
        errorText={errors.pauseEndDate?.message || serverErrors.pauseEndDate}
        value={watch("pauseEndDate")}
        {...register("pauseEndDate")}
      />
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

export default PauseForm;
