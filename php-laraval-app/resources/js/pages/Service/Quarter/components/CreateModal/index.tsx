import { Button, Flex } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import Modal from "@/components/Modal";

import Service from "@/types/service";

import { PageProps } from "@/types";

type FormValues = {
  serviceId: number;
  minSquareMeters: number;
  maxSquareMeters: number;
  quarters: number;
};

export interface CreateModalProps {
  services: Service[];
  isOpen: boolean;
  onClose: () => void;
}

const CreateModal = ({ services, onClose, isOpen }: CreateModalProps) => {
  const { t } = useTranslation();

  const { errors: serverErrors } = usePage<PageProps>().props;
  const {
    register,
    reset,
    watch,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();

  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);
    router.post("/services/quarters", values, {
      onFinish: () => {
        setIsSubmitting(false);
      },
      onSuccess: () => {
        onClose();
      },
    });
  });

  const serviceOptions = useMemo(
    () =>
      services.map((item) => ({
        label: item.name,
        value: item.id,
      })),
    [services],
  );

  useEffect(() => {
    if (isOpen) {
      reset();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen]);

  return (
    <Modal
      title={t("create service quarter")}
      isOpen={isOpen}
      onClose={onClose}
    >
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
        <Autocomplete
          options={serviceOptions}
          labelText={t("service")}
          errorText={errors.serviceId?.message || serverErrors.serviceId}
          value={watch("serviceId")}
          {...register("serviceId", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <Flex gap={4}>
          <Input
            type="number"
            labelText={t("min square meters")}
            errorText={
              errors.minSquareMeters?.message || serverErrors.minSquareMeters
            }
            {...register("minSquareMeters", {
              required: t("validation field required"),
            })}
            isRequired
          />
          <Input
            type="number"
            labelText={t("max square meters")}
            errorText={
              errors.maxSquareMeters?.message || serverErrors.maxSquareMeters
            }
            {...register("maxSquareMeters", {
              required: t("validation field required"),
            })}
            isRequired
          />
        </Flex>
        <Input
          type="number"
          labelText={t("quarters")}
          errorText={errors.quarters?.message || serverErrors.quarters}
          {...register("quarters", {
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
            {t("submit")}
          </Button>
        </Flex>
      </Flex>
    </Modal>
  );
};

export default CreateModal;
