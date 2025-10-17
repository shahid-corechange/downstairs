import { Button, Flex, Text, Textarea } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import FileInput from "@/components/FileInput";
import Input from "@/components/Input";
import Modal from "@/components/Modal";

import { validateFilesExtension, validateFilesSize } from "@/utils/validation";

import { PageProps } from "@/types";

interface FormValues {
  name: {
    sv_SE: string;
    en_US: string;
  };
  description: {
    sv_SE: string;
    en_US: string;
  };
  thumbnailImage?: FileList;
  thumbnail?: File;
}

export interface CreateModalProps {
  isOpen: boolean;
  onClose: () => void;
}

const CreateModal = ({ onClose, isOpen }: CreateModalProps) => {
  const { t } = useTranslation();

  const { errors: serverErrors } = usePage<PageProps>().props;
  const {
    register,
    reset,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = formSubmitHandler(({ thumbnailImage, ...values }) => {
    const newValues = {
      ...values,
      thumbnail:
        thumbnailImage && thumbnailImage.length > 0
          ? thumbnailImage[0]
          : undefined,
    };

    setIsSubmitting(true);
    router.post("/categories", newValues, {
      onFinish: () => setIsSubmitting(false),
      onSuccess: () => onClose(),
    });
  });

  useEffect(() => {
    if (isOpen) {
      reset();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen]);

  return (
    <Modal title={t("create category")} isOpen={isOpen} onClose={onClose}>
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
        <Flex gap={4}>
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
              {t("language sv_se")}
            </Text>
            <Input
              labelText={t("name")}
              errorText={
                errors.name?.sv_SE?.message || serverErrors["name.sv_SE"]
              }
              {...register("name.sv_SE", {
                required: t("validation field required"),
              })}
              isRequired
            />
            <Input
              as={Textarea}
              labelText={t("description")}
              errorText={
                errors.description?.sv_SE?.message ||
                serverErrors["description.sv_SE"]
              }
              resize="none"
              {...register("description.sv_SE", {
                required: t("validation field required"),
              })}
              isRequired
            />
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
              {t("language en_us")}
            </Text>
            <Input
              labelText={t("name")}
              errorText={
                errors.name?.en_US?.message || serverErrors["name.en_US"]
              }
              {...register("name.en_US", {
                required: t("validation field required"),
              })}
              isRequired
            />
            <Input
              as={Textarea}
              labelText={t("description")}
              errorText={
                errors.description?.en_US?.message ||
                serverErrors["description.en_US"]
              }
              resize="none"
              {...register("description.en_US", {
                required: t("validation field required"),
              })}
              isRequired
            />
          </Flex>
        </Flex>
        <FileInput
          labelText={t("thumbnail")}
          errorText={errors.thumbnailImage?.message || serverErrors.thumbnail}
          previewContainer={{ bg: "gray.500" }}
          description={t("input svg thumbnail description")}
          accept="image/svg+xml"
          {...register("thumbnailImage", {
            validate: {
              size: (v) => validateFilesSize(v),
              extension: (v) => validateFilesExtension(v, ["image/svg+xml"]),
            },
          })}
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
