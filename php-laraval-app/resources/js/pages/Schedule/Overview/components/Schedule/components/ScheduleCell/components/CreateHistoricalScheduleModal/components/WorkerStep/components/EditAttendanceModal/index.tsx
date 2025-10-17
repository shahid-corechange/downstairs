import { Button, Flex } from "@chakra-ui/react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Input from "@/components/Input";
import Modal from "@/components/Modal";

import { toDayjs } from "@/utils/datetime";

import { WorkerAttendance } from "../../../../types";

interface FormValues {
  startAt: string;
  endAt: string;
}

interface EditAttendanceModalProps {
  data: WorkerAttendance;
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (data: WorkerAttendance) => void;
}

const EditAttendanceModal = ({
  data,
  isOpen,
  onClose,
  onSubmit,
}: EditAttendanceModalProps) => {
  const { t } = useTranslation();

  const {
    register,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    defaultValues: {
      startAt: toDayjs(data.startAt).format("YYYY-MM-DDTHH:mm"),
      endAt: toDayjs(data.endAt).format("YYYY-MM-DDTHH:mm"),
    },
  });

  const handleSubmit = formSubmitHandler((values) => {
    onSubmit({
      ...data,
      startAt: toDayjs(toDayjs().format(`${values.startAt}:00Z`)).toISOString(),
      endAt: toDayjs(toDayjs().format(`${values.endAt}:00Z`)).toISOString(),
    });
    onClose();
  });

  return (
    <Modal title={t("edit attendance")} onClose={onClose} isOpen={isOpen}>
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
        <Input
          type="datetime-local"
          labelText={t("schedule start")}
          errorText={errors.startAt?.message}
          {...register("startAt", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <Input
          type="datetime-local"
          labelText={t("schedule end")}
          errorText={errors.endAt?.message}
          {...register("endAt", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <Flex justify="right" mt={4} gap={4}>
          <Button colorScheme="gray" fontSize="sm" onClick={onClose}>
            {t("close")}
          </Button>
          <Button type="submit" fontSize="sm">
            {t("save")}
          </Button>
        </Flex>
      </Flex>
    </Modal>
  );
};

export default EditAttendanceModal;
