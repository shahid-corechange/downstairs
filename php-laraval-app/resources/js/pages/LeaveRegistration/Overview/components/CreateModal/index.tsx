import { Button, Flex, useConst, useDisclosure } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import AlertDialog from "@/components/AlertDialog";
import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import Modal from "@/components/Modal";

import { DATE_FORMAT } from "@/constants/datetime";
import { LEAVE_TYPES } from "@/constants/leaveTypes";

import { getTranslatedOptions } from "@/utils/autocomplete";
import { toDayjs } from "@/utils/datetime";

import { PageProps } from "@/types";

import { EmployeeList } from "../../types";
import { getAdjustedEndAt, getAdjustedStartAt } from "../../utils";

type FormValues = {
  type: string;
  employeeId: number;
  startAt: string;
  endAt: string;
};

export interface CreateModalProps {
  employees: EmployeeList[];
  isOpen: boolean;
  onClose: () => void;
}

const CreateModal = ({ employees, onClose, isOpen }: CreateModalProps) => {
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

  const {
    isOpen: isAlertOpen,
    onOpen: onAlertOpen,
    onClose: onAlertClose,
  } = useDisclosure();

  const type = watch("type");
  const startAt = watch("startAt");
  const endAt = watch("endAt");

  const leaveOptions = useConst(getTranslatedOptions(LEAVE_TYPES));

  const employeeOptions = useMemo(
    () =>
      employees.map((item) => {
        return {
          label: item.name,
          value: item.id,
        };
      }),
    [employees],
  );

  const isStartHistorical = useMemo(() => {
    if (!startAt) {
      return false;
    }

    const startAtDayjs = getAdjustedStartAt(startAt, false);
    const endOfLastMonth = toDayjs().subtract(1, "month").endOf("month");

    return startAtDayjs.isBefore(endOfLastMonth);
  }, [startAt]);

  const lastCreatedAt = useMemo(() => {
    const endOfLastMonth = toDayjs().subtract(1, "month").endOf("month");

    if (!endAt) {
      return endOfLastMonth;
    }

    const endAtDayjs = getAdjustedEndAt(endAt, false);

    return endAtDayjs.isBefore(endOfLastMonth) ? endAtDayjs : endOfLastMonth;
  }, [endAt]);

  const handleSubmit = formSubmitHandler(({ startAt, endAt, ...values }) => {
    setIsSubmitting(true);

    router.post(
      "/leave-registrations",
      {
        ...values,
        startAt: toDayjs(startAt, false).toISOString(),
        endAt: endAt ? toDayjs(endAt, false).toISOString() : null,
      },
      {
        onFinish: () => {
          setIsSubmitting(false);
          onAlertClose();
        },
        onSuccess: onClose,
      },
    );
  });

  useEffect(() => {
    if (isOpen) {
      reset();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen]);

  return (
    <>
      <Modal
        title={t("create leave registration")}
        isOpen={isOpen}
        onClose={onClose}
      >
        <Flex
          as="form"
          direction="column"
          gap={4}
          onSubmit={(e) => {
            e.preventDefault();

            if (isStartHistorical) {
              onAlertOpen();
              return;
            }

            handleSubmit();
          }}
          autoComplete="off"
          noValidate
        >
          <Autocomplete
            options={employeeOptions}
            labelText={t("employee")}
            errorText={errors.employeeId?.message || serverErrors.employeeId}
            value={watch("employeeId")}
            {...register("employeeId", {
              required: t("validation field required"),
            })}
            isRequired
          />
          <Autocomplete
            options={leaveOptions}
            labelText={t("type")}
            errorText={errors.type?.message || serverErrors.type}
            value={type}
            {...register("type", {
              required: t("validation field required"),
            })}
            isRequired
          />
          <Flex gap={4}>
            <Input
              type="datetime-local"
              labelText={t("start at")}
              helperText={t("leave registration start at helper text")}
              errorText={errors.startAt?.message || serverErrors.startAt}
              {...register("startAt", {
                required: t("validation field required"),
              })}
              isRequired
            />
            <Input
              type="datetime-local"
              labelText={t("end at")}
              helperText={t("leave registration end at helper text")}
              errorText={errors.endAt?.message || serverErrors.endAt}
              {...register("endAt", {
                required: ["SEM", "TJL"].includes(type)
                  ? t("validation field required")
                  : undefined,
              })}
              isRequired={["SEM", "TJL"].includes(type)}
            />
          </Flex>
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
      <AlertDialog
        title={t("create leave registration")}
        size="2xl"
        confirmButton={{
          isLoading: isSubmitting,
          loadingText: t("please wait"),
        }}
        confirmText={t("continue")}
        isOpen={isAlertOpen}
        onClose={onAlertClose}
        onConfirm={handleSubmit}
      >
        {isStartHistorical && (
          <Alert
            status="info"
            title={t("info")}
            message={t("historical leave registration modal alert info", {
              date: lastCreatedAt.format(DATE_FORMAT),
            })}
            fontSize="small"
            mb={6}
          />
        )}
        {t("leave registration create alert body")}
      </AlertDialog>
    </>
  );
};

export default CreateModal;
