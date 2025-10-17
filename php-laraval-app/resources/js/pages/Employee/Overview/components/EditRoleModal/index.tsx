import { Button, Flex, useConst } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import Checkboxes from "@/components/Checkboxes";
import Modal from "@/components/Modal";

import User from "@/types/user";

import { PageProps } from "@/types";

import { EmployeeProps } from "../../types";

type FormValues = {
  roles: string[];
};

export interface EditRoleModalProps {
  isOpen: boolean;
  onClose: () => void;
  data?: User;
}

const EditRoleModal = ({ data, onClose, isOpen }: EditRoleModalProps) => {
  const { t } = useTranslation();
  const { roles, errors: serverErrors } =
    usePage<PageProps<EmployeeProps>>().props;

  const {
    register,
    reset,
    watch,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();
  const roleOptions = useConst(roles.map((role) => role.name));

  const [isSubmitting, setIsSubmitting] = useState(false);
  const selectedRoles = watch("roles");

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);
    router.put(`/employees/${data?.id}/roles`, values, {
      onFinish: () => setIsSubmitting(false),
      onSuccess: onClose,
    });
  });

  useEffect(() => {
    if (selectedRoles && selectedRoles.includes("Superadmin")) {
      reset({
        roles: ["Superadmin"],
      });
    }
  }, [selectedRoles]);

  useEffect(() => {
    reset({
      roles: data?.roles?.map((role) => role.name) ?? [],
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data]);

  return (
    <Modal title={t("edit role")} isOpen={isOpen} onClose={onClose}>
      <Alert
        status="info"
        title={t("info")}
        message={t("edit role info")}
        fontSize="small"
        mb={6}
      />
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
        <Checkboxes
          options={roleOptions}
          labelText={t("roles")}
          errorText={errors.roles?.message || serverErrors.roles}
          value={selectedRoles}
          isReadOnly={(option) =>
            selectedRoles.includes("Superadmin") &&
            option.value !== "Superadmin"
          }
          {...register("roles", {
            required: t("validation field required"),
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

export default EditRoleModal;
