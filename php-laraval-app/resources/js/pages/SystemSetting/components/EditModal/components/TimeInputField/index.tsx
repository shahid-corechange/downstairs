import { usePage } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import TimePicker from "@/components/TimePicker";

import { ValueFieldProps } from "../../types";

const TimeInputField = ({
  errors,
  register,
  watch,
}: Omit<ValueFieldProps, "setValue">) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;

  return (
    <TimePicker
      labelText={t("value")}
      errorText={errors.value?.message || serverErrors.value}
      value={watch("value")}
      {...register("value")}
    />
  );
};

export default TimeInputField;
