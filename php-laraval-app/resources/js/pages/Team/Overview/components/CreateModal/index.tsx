import { Button, Flex, Textarea } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import ColorPicker from "@/components/ColorPicker";
import FileInput from "@/components/FileInput";
import Input from "@/components/Input";
import Modal from "@/components/Modal";

import User from "@/types/user";

import { validateFilesExtension, validateFilesSize } from "@/utils/validation";

import { PageProps } from "@/types";

interface FormValues {
  name: string;
  color: string;
  userIds: string;
  description: string;
  thumbnailImage?: FileList;
  thumbnail?: File;
}

export interface CreateModalProps {
  workers: User[];
  isOpen: boolean;
  onClose: () => void;
}

const CreateModal = ({ workers, onClose, isOpen }: CreateModalProps) => {
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

  const handleSubmit = formSubmitHandler(({ thumbnailImage, ...values }) => {
    const newValues = {
      ...values,
      userIds: JSON.parse(values.userIds),
    };

    if (thumbnailImage && thumbnailImage.length > 0) {
      const file = thumbnailImage[0];
      newValues.thumbnail = file;
    }

    setIsSubmitting(true);
    router.post("/teams", newValues, {
      onFinish: () => {
        setIsSubmitting(false);
      },
      onSuccess: () => {
        onClose();
      },
    });
  });

  const workerOptions = useMemo(
    () =>
      workers.map((item) => ({
        label: item.fullname,
        value: item.id,
      })),
    [workers],
  );

  useEffect(() => {
    if (isOpen) {
      reset();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen]);

  return (
    <Modal title={t("create team")} isOpen={isOpen} onClose={onClose}>
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
        <Input
          labelText={t("name")}
          errorText={errors.name?.message || serverErrors.name}
          isRequired
          {...register("name", { required: t("validation field required") })}
        />
        <ColorPicker
          isRequired
          labelText={t("color")}
          value={watch("color")}
          errorText={errors.color?.message || serverErrors.color}
          {...register("color", { required: t("validation field required") })}
        />
        <Autocomplete
          options={workerOptions}
          labelText={t("worker")}
          helperText={t("employee must assigned to worker role")}
          errorText={errors.userIds?.message || serverErrors.userIds}
          value={watch("userIds")}
          {...register("userIds", {
            required: t("validation field required"),
          })}
          isRequired
          multiple
        />
        <Input
          as={Textarea}
          labelText={t("description")}
          errorText={errors.description?.message || serverErrors.description}
          resize="none"
          {...register("description")}
        />
        <FileInput
          labelText={t("thumbnail")}
          helperText={t("use svg format for best result")}
          errorText={errors.thumbnailImage?.message || serverErrors.thumbnail}
          accept="image/jpg, image/jpeg, image/png, image/svg+xml"
          {...register("thumbnailImage", {
            validate: {
              size: (v) => validateFilesSize(v),
              extension: (v) =>
                validateFilesExtension(v, [
                  "image/jpg",
                  "image/jpeg",
                  "image/png",
                  "image/svg+xml",
                ]),
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
