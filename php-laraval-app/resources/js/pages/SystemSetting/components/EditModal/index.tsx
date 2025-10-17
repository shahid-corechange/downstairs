import { Button, Flex, Textarea } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import Modal from "@/components/Modal";

import { INVOICE_DUE_DAYS } from "@/constants/invoice";

import GlobalSetting from "@/types/globalSetting";

import { parseValueByType } from "@/utils/parser";

import TeamAutocompleteField from "./components/TeamAutocompleteField";
import TimeInputField from "./components/TimeInputField";
import { FormValues } from "./types";

export interface EditModalProps {
  isOpen: boolean;
  onClose: () => void;
  data?: GlobalSetting;
  refillSequences: Record<number, string>;
}

const EditModal = ({
  data,
  onClose,
  isOpen,
  refillSequences,
}: EditModalProps) => {
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

  const valueField = useMemo(() => {
    switch (data?.key) {
      case "DEFAULT_SHOWN_TEAM":
        return (
          <TeamAutocompleteField
            errors={errors}
            register={register}
            watch={watch}
            setValue={setValue}
          />
        );
      case "DEFAULT_MIN_HOUR_SHOW":
      case "DEFAULT_MAX_HOUR_SHOW":
        return (
          <TimeInputField errors={errors} register={register} watch={watch} />
        );
      case "INVOICE_DUE_DAYS":
        return (
          <Autocomplete
            options={INVOICE_DUE_DAYS}
            value={data?.value}
            {...register("value")}
          />
        );
      case "SUBSCRIPTION_REFILL_SEQUENCE":
        return (
          <Autocomplete
            options={Object.entries(refillSequences).map(([key, value]) => ({
              label: value,
              value: key,
            }))}
            value={data?.value}
            {...register("value")}
          />
        );
      default:
        return (
          <Input
            labelText={t("value")}
            errorText={errors.value?.message || serverErrors.value}
            {...register("value")}
          />
        );
    }
  }, [data]);

  const handleSubmit = formSubmitHandler((values) => {
    const newValue = parseValueByType(values.value, data?.type);
    setIsSubmitting(true);
    router.patch(
      `/system-settings/${data?.id}`,
      { key: values.key, value: newValue },
      {
        onFinish: () => setIsSubmitting(false),
        onSuccess: () => onClose(),
      },
    );
  });

  useEffect(() => {
    reset({
      key: data?.key,
      value: data?.value,
      description: data?.description,
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data]);

  return (
    <Modal title={t("edit system settings")} onClose={onClose} isOpen={isOpen}>
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
        <Alert
          status="warning"
          title={t("info")}
          message={t("system settings edit warning")}
          fontSize="small"
          mb={6}
        />
        <Input
          labelText={t("key")}
          errorText={errors.key?.message || serverErrors.key}
          {...register("key")}
          isDisabled
        />
        {valueField}
        <Input
          as={Textarea}
          labelText={t("description")}
          errorText={errors.description?.message || serverErrors.description}
          resize="none"
          {...register("description")}
          isDisabled
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
            {t("submit")}
          </Button>
        </Flex>
      </Flex>
    </Modal>
  );
};

export default EditModal;
