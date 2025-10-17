import { Button, Flex, Textarea } from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import Autocomplete from "@/components/Autocomplete";
import ColorPicker from "@/components/ColorPicker";
import FileInput from "@/components/FileInput";
import Input from "@/components/Input";
import Modal from "@/components/Modal";
import ScheduleCollisionModal from "@/components/ScheduleCollisionModal";

import Schedule from "@/types/schedule";
import Team from "@/types/team";
import User from "@/types/user";

import { validateFilesExtension, validateFilesSize } from "@/utils/validation";

import { PageProps } from "@/types";

type FormValues = {
  name: string;
  color: string;
  userIds: string;
  description: string;
  isActive: boolean;
  thumbnailImage?: FileList;
  thumbnail?: File;
};

export interface EditModalProps {
  workers: User[];
  isOpen: boolean;
  onClose: () => void;
  data?: Team;
}

const EditModal = ({ data, workers, onClose, isOpen }: EditModalProps) => {
  const { t } = useTranslation();

  const { errors: serverErrors } = usePage().props;
  const {
    register,
    reset,
    watch,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [collidedSchedules, setCollidedSchedules] = useState<Schedule[]>([]);

  const handleSubmit = formSubmitHandler(({ thumbnailImage, ...values }) => {
    const newValues = {
      ...values,
      userIds: values.userIds ? JSON.parse(values.userIds) : [],
    };

    if (thumbnailImage && thumbnailImage.length > 0) {
      const file = thumbnailImage[0];
      newValues.thumbnail = file;
    }

    setIsSubmitting(true);
    router.post(
      `/teams/${data?.id}`,
      { _method: "PATCH", ...newValues },
      {
        onFinish: () => setIsSubmitting(false),
        onSuccess: (page) => {
          const {
            flash: { error, errorPayload },
          } = (
            page as Page<
              PageProps<Record<string, unknown>, unknown, Schedule[]>
            >
          ).props;

          if (error) {
            setCollidedSchedules(errorPayload ?? []);
            return;
          }

          onClose();
        },
      },
    );
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
    reset({
      name: data?.name,
      color: data?.color,
      description: data?.description,
      isActive: data?.isActive,
      userIds: JSON.stringify(data?.users?.map((user) => user.id) ?? []),
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data]);

  return (
    <>
      <Modal title={t("edit team")} onClose={onClose} isOpen={isOpen}>
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
            title={t("warning")}
            message={t("edit team warning")}
            fontSize="small"
            mb={6}
          />
          <Input
            isRequired
            labelText={t("name")}
            errorText={errors.name?.message || serverErrors.name}
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
            isRequired
            options={workerOptions}
            labelText={`${t("workers")} (${data?.users?.length ?? 1})`}
            helperText={t("employee must assigned to worker role")}
            value={watch("userIds")}
            errorText={errors.userIds?.message || serverErrors.userIds}
            {...register("userIds", {
              required: t("validation field required"),
            })}
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
            preview={data?.avatar}
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
      <ScheduleCollisionModal
        isOpen={collidedSchedules.length > 0}
        onClose={() => setCollidedSchedules([])}
        data={collidedSchedules}
      />
    </>
  );
};

export default EditModal;
