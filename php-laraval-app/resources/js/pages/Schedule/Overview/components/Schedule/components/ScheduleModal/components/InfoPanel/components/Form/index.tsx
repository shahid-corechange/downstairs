import { Button, Flex, Textarea } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import { AutocompleteOption } from "@/components/Autocomplete/types";
import Input from "@/components/Input";

import { useError } from "@/hooks/error";
import { usePageModal } from "@/hooks/modal";

import { ScheduleOverviewPageProps } from "@/pages/Schedule/Overview/types";

import { useEditScheduleMutation } from "@/services/schedule";

import { Response } from "@/types/api";
import Schedule from "@/types/schedule";

import { renderHighlightedText } from "@/utils/autocomplete";
import { toDayjs } from "@/utils/datetime";
import { isValidQuarterTime } from "@/utils/validation";

import { PageProps } from "@/types";

import RefundCreditConfirmation from "./components/RefundCreditConfirmation";
import RescheduleLaundryConfirmation from "./components/RescheduleLaundryConfirmation";
import UseCreditModal from "./components/UseCreditModal";
import { NewAddon } from "./types";

interface SelectedProduct {
  id: number;
  name: string;
  creditPrice: number;
}

interface FormValues {
  teamId?: number;
  addons: string;
  startAt: string;
  endAt: string;
  note?: string;
}

interface InfoPanelFormProps {
  schedule: Schedule;
  onCancel: () => void;
  onSuccess: (data: Schedule, response: Response<Schedule>) => void;
}

const InfoPanelForm = ({
  schedule,
  onCancel,
  onSuccess,
}: InfoPanelFormProps) => {
  const { t } = useTranslation();
  const { addons, teams } =
    usePage<PageProps<ScheduleOverviewPageProps>>().props;
  const { validationError } = useError();
  const editScheduleMutation = useEditScheduleMutation();

  const [totalRefundCredit, setTotalRefundCredit] = useState(0);
  const [totalUseCredit, setTotalUseCredit] = useState(0);
  const [quarters, setQuarters] = useState(schedule.quarters);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [addedProducts, setAddedProducts] = useState<NewAddon[]>([]);
  const [removedProduct, setRemovedProduct] = useState<number[]>([]);

  const { modal, openModal, closeModal } = usePageModal<
    unknown,
    "useCredit" | "refundCredit" | "rescheduleLaundry"
  >();

  const {
    register,
    watch,
    getValues,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    values: {
      teamId: schedule?.teamId,
      addons: JSON.stringify(
        (schedule.items ?? []).reduce<number[]>((acc, value) => {
          if (!value.item?.deletedAt) {
            acc.push(value.itemableId);
          }
          return acc;
        }, []),
      ),
      startAt: toDayjs(schedule.startAt).format("YYYY-MM-DDTHH:mm"),
      endAt: toDayjs(schedule.endAt).format("YYYY-MM-DDTHH:mm"),
      note: schedule.notes?.note ?? "",
    },
  });

  const addonsValue = watch("addons");
  const startAt = watch("startAt");
  const endAt = watch("endAt");
  const userCredit = schedule?.user?.totalCredits ?? 0;

  const laundryType = useMemo(() => {
    if (!schedule?.detail || !("laundryType" in schedule.detail)) {
      return;
    }

    return t(schedule.detail.laundryType ?? "");
  }, [schedule]);

  const onClose = () => {
    setIsSubmitting(false);
    closeModal();
  };

  const handleCreditSubmit = () => {
    if (laundryType) {
      openModal("rescheduleLaundry");
      return;
    }

    editSchedule();
  };

  const editSchedule = (
    onSettled?: () => void,
    addonsUsingCredit?: number[],
  ) => {
    const values = getValues();

    const customAddedProducts = addedProducts.map((addon) =>
      addonsUsingCredit?.includes(addon.addonId)
        ? {
            ...addon,
            useCredit: true,
          }
        : addon,
    );

    editScheduleMutation.mutate(
      {
        teamId: values?.teamId,
        startAt: toDayjs(`${values.startAt}:00`, false).toISOString(),
        endAt: toDayjs(`${values.endAt}:00`, false).toISOString(),
        scheduleId: schedule.id,
        note: values.note,
        removeAddOns: removedProduct,
        newAddOns: addonsUsingCredit ? customAddedProducts : addedProducts,
      },
      {
        onSettled: () => {
          setIsSubmitting(false);
          onSettled?.();
        },
        onSuccess: ({ data, response }) => {
          onCancel();
          onSuccess(data, response);
        },
      },
    );
  };

  const totalActiveWorkers = useMemo(() => {
    return (schedule.allEmployees ?? []).filter(
      (worker) => !worker.deletedAt || worker.status !== "cancel",
    ).length;
  }, [schedule?.allEmployees]);

  const teamOptions = useMemo(
    () =>
      teams.reduce<AutocompleteOption[]>((acc, team) => {
        acc.push({ label: team.name, value: team.id });
        return acc;
      }, []),
    [teams],
  );

  const addonOptions = useMemo(
    () =>
      addons.reduce<AutocompleteOption[]>((acc, value) => {
        if (
          value.services?.some((service) => service.id === schedule?.serviceId)
        ) {
          const useCredit = (schedule.items ?? []).some(
            (item) =>
              item.itemableId === value.id && item.paymentMethod === "credit",
          );
          acc.push({
            label: `${value.name}${useCredit ? ` (${t("credit")})` : ""}`,
            value: value.id,
          });
        }
        return acc;
      }, []),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [schedule, addons],
  );

  const selectedProducts: SelectedProduct[] = useMemo(
    () =>
      addonsValue
        ? JSON.parse(addonsValue).map((addOnId: number) => {
            const addOn = addons.find((addon) => addon.id === addOnId);
            if (addOn) {
              return {
                id: addOnId,
                name: addOn?.name ?? "",
                creditPrice: addOn?.creditPrice ?? 0,
              };
            }
          })
        : [],
    [addons, addonsValue],
  );

  const handleSubmit = formSubmitHandler(() => {
    setIsSubmitting(true);

    if (totalUseCredit > 0 && userCredit > 0) {
      openModal("useCredit");
      return;
    }

    if (totalRefundCredit > 0) {
      openModal("refundCredit");
      return;
    }

    if (laundryType) {
      openModal("rescheduleLaundry");
      return;
    }

    editSchedule();
  });

  useEffect(() => {
    let useCredit = 0;
    let refundTotal = 0;

    const newAddedProducts = selectedProducts.reduce<NewAddon[]>(
      (acc, value) => {
        if (
          !(schedule.items ?? []).some((item) => item.itemableId === value.id)
        ) {
          acc.push({
            addonId: value.id,
            name: value.name,
            quantity: 1,
            useCredit: false,
            creditPrice: value.creditPrice,
          });
          useCredit += value.creditPrice;
        }
        return acc;
      },
      [],
    );

    const newRemovedProducts = (schedule.items ?? []).reduce<number[]>(
      (acc, value) => {
        if (
          !selectedProducts.some((product) => product.id === value.itemableId)
        ) {
          acc.push(value.itemableId);

          if (value.paymentMethod === "credit") {
            refundTotal += value.item?.creditPrice ?? 0;
          }
        }
        return acc;
      },
      [],
    );

    setAddedProducts(newAddedProducts);
    setRemovedProduct(newRemovedProducts);
    setTotalUseCredit(useCredit);
    setTotalRefundCredit(refundTotal);
  }, [selectedProducts, schedule?.items]);

  useEffect(() => {
    if (!startAt || !endAt) {
      return;
    }

    const startAtDayjs = toDayjs(startAt, false);
    const endAtDayjs = toDayjs(endAt, false);

    const calendarQuarters = Math.ceil(
      endAtDayjs.diff(startAtDayjs, "minute") / 15,
    );
    setQuarters(calendarQuarters * totalActiveWorkers);
  }, [totalActiveWorkers, schedule, startAt, endAt]);

  return (
    <>
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
        <Autocomplete
          options={teamOptions}
          labelText={t("team")}
          errorText={validationError.team}
          value={watch("teamId")}
          {...register("teamId", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <Autocomplete
          options={addonOptions}
          labelText={t("add ons")}
          errorText={validationError.addons}
          value={watch("addons")}
          {...register("addons")}
          renderOption={(option, filter) => {
            if (typeof option === "string") {
              return null;
            }

            const label = option.label.replace(` (${t("credit")})`, "");
            return renderHighlightedText(label, filter);
          }}
          multiple
        />
        <Flex gap={4}>
          <Input
            type="datetime-local"
            labelText={t("start at")}
            errorText={errors.startAt?.message || validationError.startAt}
            {...register("startAt", {
              required: t("validation field required"),
              validate: {
                quarter: isValidQuarterTime,
              },
            })}
            isRequired
          />
          <Input
            type="datetime-local"
            labelText={t("end at")}
            errorText={errors.endAt?.message || validationError.endAt}
            {...register("endAt", {
              required: t("validation field required"),
              validate: {
                quarter: isValidQuarterTime,
              },
            })}
            isRequired
          />
        </Flex>
        <Input
          type="number"
          labelText={t("quarters")}
          value={quarters}
          isReadOnly
        />
        <Flex gap={4}>
          <Input
            as={Textarea}
            labelText={t("key information with platform")}
            helperText={t("property key information helper text")}
            defaultValue={schedule.keyInformation}
            resize="none"
            isReadOnly
          />
          <Input
            as={Textarea}
            labelText={t("property note with platform")}
            helperText={t("property note helper text")}
            defaultValue={schedule.notes?.propertyNote}
            resize="none"
            isReadOnly
          />
        </Flex>
        <Flex gap={4}>
          <Input
            as={Textarea}
            labelText={t("subscription note with platform")}
            helperText={t("subscription note helper text")}
            defaultValue={schedule.notes?.subscriptionNote}
            resize="none"
            isReadOnly
          />
          <Input
            as={Textarea}
            labelText={t("schedule note with platform")}
            helperText={t("schedule note helper text")}
            errorText={validationError.note}
            resize="none"
            {...register("note")}
          />
        </Flex>
        <Flex justify="right" mt={4} gap={4}>
          <Button colorScheme="gray" fontSize="sm" onClick={onCancel}>
            {t("close")}
          </Button>
          <Button
            type="submit"
            fontSize="sm"
            isLoading={isSubmitting}
            loadingText={t("please wait")}
          >
            {t("save")}
          </Button>
        </Flex>
      </Flex>

      <UseCreditModal
        addons={addedProducts}
        userCredit={userCredit}
        refundCredit={totalRefundCredit}
        isOpen={modal === "useCredit"}
        handleSubmit={handleCreditSubmit}
        onClose={onClose}
      />

      <RefundCreditConfirmation
        refundCredit={totalRefundCredit}
        isOpen={modal === "refundCredit"}
        handleSubmit={handleCreditSubmit}
        onClose={onClose}
      />

      <RescheduleLaundryConfirmation
        type={laundryType}
        isOpen={modal === "rescheduleLaundry"}
        handleSubmit={editSchedule}
        onClose={onClose}
      />
    </>
  );
};

export default InfoPanelForm;
