import { TabPanel, TabPanelProps, Textarea } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { FieldErrors, UseFormRegister } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Input from "@/components/Input";

import { FormValues } from "../../types";

interface PropertyPanelProps extends TabPanelProps {
  register: UseFormRegister<FormValues>;
  errors: FieldErrors<FormValues>;
}

const PropertyPanel = ({ register, errors, ...props }: PropertyPanelProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;

  return (
    <TabPanel {...props}>
      <Input
        type="number"
        min={1}
        labelText={t("square meter")}
        errorText={errors.squareMeter?.message || serverErrors.squareMeter}
        isRequired
        {...register("squareMeter", {
          required: t("validation field required"),
          min: { value: 1, message: t("validation field min", { min: 1 }) },
          valueAsNumber: true,
        })}
      />
      <Input
        as={Textarea}
        labelText={t("note")}
        helperText={t("property note helper text")}
        errorText={errors.note?.message || serverErrors.meta?.note}
        resize="none"
        {...register("note")}
      />
    </TabPanel>
  );
};

export default PropertyPanel;
