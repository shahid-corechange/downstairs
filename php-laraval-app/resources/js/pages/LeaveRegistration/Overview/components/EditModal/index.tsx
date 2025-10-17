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

import { DATETIME_FORMAT, DATE_FORMAT } from "@/constants/datetime";
import { LEAVE_TYPES } from "@/constants/leaveTypes";

import LeaveRegistration from "@/types/leaveRegistration";

import { getTranslatedOptions } from "@/utils/autocomplete";
import { toDayjs } from "@/utils/datetime";

import { getAdjustedEndAt, getAdjustedStartAt } from "../../utils";

interface FormValues {
  type: string;
  startAt: string;
  endAt: string;
}

export interface EditModalProps {
  isOpen: boolean;
  onClose: () => void;
  data?: LeaveRegistration;
}

const EditModal = ({ data, isOpen, onClose }: EditModalProps) => {
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

  const {
    isOpen: isAlertOpen,
    onOpen: onAlertOpen,
    onClose: onAlertClose,
  } = useDisclosure();

  const type = watch("type");
  const startAt = watch("startAt");
  const endAt = watch("endAt");

  const startAtDayjs = getAdjustedStartAt(startAt, false);
  const originalStartAtDayjs = getAdjustedStartAt(data?.startAt);
  const endAtDayjs = getAdjustedEndAt(endAt, false);

  const leaveOptions = useConst(getTranslatedOptions(LEAVE_TYPES));

  const isStartHistorical = useMemo(() => {
    if (!startAt) {
      return false;
    }

    const endOfLastMonth = toDayjs().subtract(1, "month").endOf("month");

    return startAtDayjs.isBefore(endOfLastMonth);
  }, [startAt, startAtDayjs]);

  const isStartMovedToTheLaterDate = useMemo(() => {
    if (!startAt) {
      return false;
    }

    return startAtDayjs.isAfter(originalStartAtDayjs);
  }, [startAt, startAtDayjs, originalStartAtDayjs]);

  const lastCreatedAt = useMemo(() => {
    const endOfLastMonth = toDayjs().subtract(1, "month").endOf("month");

    if (!endAt) {
      return endOfLastMonth;
    }

    return endAtDayjs.isBefore(endOfLastMonth) ? endAtDayjs : endOfLastMonth;
  }, [endAt, endAtDayjs]);

  const isTypeChanged = useMemo(() => {
    if (!data) {
      return false;
    }

    const startOfThisMonth = toDayjs().startOf("month");

    return (
      data.type !== type && originalStartAtDayjs.isBefore(startOfThisMonth)
    );
  }, [data, type, originalStartAtDayjs]);

  const handleSubmit = formSubmitHandler(({ startAt, endAt, ...values }) => {
    setIsSubmitting(true);

    router.patch(
      `/leave-registrations/${data?.id}`,
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
    reset({
      type: data?.type,
      startAt: toDayjs(data?.startAt).format(DATETIME_FORMAT),
      endAt: data?.endAt ? toDayjs(data?.endAt).format(DATETIME_FORMAT) : "",
    });
  }, [data]);

  return (
    <>
      <Modal
        title={t("edit leave registration")}
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
          <Input
            labelText={t("employee")}
            defaultValue={data?.employee?.name ?? ""}
            isReadOnly
          />
          <Autocomplete
            options={leaveOptions}
            labelText={t("type")}
            errorText={errors.type?.message || serverErrors.type}
            value={watch("type")}
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
        title={t("edit leave registration")}
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
        {isStartMovedToTheLaterDate && (
          <Alert
            status="warning"
            title={t("warning")}
            message={t(
              "start moved to the later date leave registration modal alert warning",
              {
                startDate: originalStartAtDayjs.format(DATE_FORMAT),
                endDate: startAtDayjs.subtract(1, "day").format(DATE_FORMAT),
              },
            )}
            fontSize="small"
            mb={6}
          />
        )}
        {isTypeChanged && (
          <Alert
            status="warning"
            title={t("warning")}
            message={t("changed type leave registration modal alert warning", {
              startDate: (isStartMovedToTheLaterDate
                ? startAtDayjs
                : originalStartAtDayjs
              ).format(DATE_FORMAT),
              endDate: lastCreatedAt.format(DATE_FORMAT),
            })}
            fontSize="small"
            mb={6}
          />
        )}
        {t("leave registration edit alert body")}
      </AlertDialog>
    </>
  );
};

export default EditModal;
