import { Flex, TabPanel, TabPanelProps, Textarea } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useMemo } from "react";
import { FieldErrors, UseFormRegister } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import { AutocompleteOption } from "@/components/Autocomplete/types";
import Input from "@/components/Input";

import { useGetKeyPlaces } from "@/services/keyplace";

import { FormValues } from "../../types";

interface KeyPanelProps extends TabPanelProps {
  register: UseFormRegister<FormValues>;
  errors: FieldErrors<FormValues>;
  keyPlace?: string;
  initialKeyPlace?: string;
}

const KeyPanel = ({
  register,
  errors,
  keyPlace,
  initialKeyPlace,
  ...props
}: KeyPanelProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;
  const keyPlaces = useGetKeyPlaces({
    request: {
      filter: { eq: { propertyId: "null" } },
      size: -1,
    },
  });
  const initialKeys = initialKeyPlace
    ? [{ label: `${initialKeyPlace}`, value: `${initialKeyPlace}` }]
    : [];

  const keyPlaceOptions = useMemo(
    () =>
      keyPlaces.data?.reduce<AutocompleteOption[]>((acc, keyPlace) => {
        const newElement = {
          label: `${keyPlace.id}`,
          value: `${keyPlace.id}`,
        };

        const lastIndex = acc.length - 1;

        if (
          acc[lastIndex] &&
          Number(acc[lastIndex].value) < Number(newElement.value)
        ) {
          acc.push(newElement);
        } else {
          acc.splice(lastIndex, 0, newElement);
        }

        return acc;
      }, initialKeys) ?? initialKeys,

    // eslint-disable-next-line react-hooks/exhaustive-deps
    [keyPlaces.data],
  );

  return (
    <TabPanel {...props}>
      <Flex gap={4}>
        <Autocomplete
          options={keyPlaceOptions}
          labelText={t("key place")}
          helperText={t("key place helper text")}
          errorText={
            errors.keyPlace?.message || serverErrors["keyInformation.keyPlace"]
          }
          value={keyPlace}
          {...register("keyPlace")}
          isLoading={keyPlaces.isLoading}
          allowEmpty
        />
        <Input
          labelText={t("front door code")}
          errorText={
            errors.frontDoorCode?.message ||
            serverErrors["keyInformation.frontDoorCode"]
          }
          {...register("frontDoorCode")}
        />
      </Flex>
      <Flex align="center" gap={4}>
        <Input
          labelText={t("alarm code off")}
          errorText={
            errors.alarmCodeOff?.message ||
            serverErrors["keyInformation.alarmCodeOff"]
          }
          {...register("alarmCodeOff")}
        />
        <Input
          labelText={t("alarm code on")}
          errorText={
            errors.alarmCodeOn?.message ||
            serverErrors["keyInformation.alarmCodeOn"]
          }
          {...register("alarmCodeOn")}
        />
      </Flex>
      <Input
        as={Textarea}
        labelText={t("information")}
        errorText={errors.information?.message || serverErrors.note}
        {...register("information")}
        resize="none"
      />
    </TabPanel>
  );
};

export default KeyPanel;
