import { useConst } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";

import { SettingProps } from "@/pages/SystemSetting/types";

import { PageProps } from "@/types";

import { ValueFieldProps } from "../../types";

const TeamAutocompleteField = ({
  errors,
  register,
  watch,
  setValue,
}: ValueFieldProps) => {
  const { t } = useTranslation();
  const { teams, errors: serverErrors } =
    usePage<PageProps<SettingProps>>().props;

  const options = useConst(
    teams.map((team) => ({ label: team.name, value: `${team.id}` })),
  );

  const value = watch("value");

  return (
    <Autocomplete
      labelText={t("value")}
      errorText={errors.value?.message || serverErrors.value}
      options={options}
      value={value ? JSON.stringify(value.split(",")) : undefined}
      {...register("value")}
      onChange={(e) => setValue("value", JSON.parse(e.target.value).join(","))}
      multiple
    />
  );
};

export default TeamAutocompleteField;
