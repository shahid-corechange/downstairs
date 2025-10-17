import { Button, Flex, Textarea, useConst } from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";
import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import Modal from "@/components/Modal";
import ScheduleCollisionModal from "@/components/ScheduleCollisionModal";
import TimePicker from "@/components/TimePicker";

import {
  DATE_FORMAT,
  SIMPLE_TIME_FORMAT,
  TIME_FORMAT,
} from "@/constants/datetime";

import { useGetServices } from "@/services/service";

import Schedule from "@/types/schedule";
import UnassignSubscription from "@/types/unassignSubscription";

import { toDayjs, toUTC } from "@/utils/datetime";

import { PageProps } from "@/types";

import {
  CleaningDetailPayload,
  LaundryDetailPayload,
  UnassignSubscriptionPageProps,
} from "../../types";

interface FormValues {
  teamId: number;
  isFixed: string;
  startAt: string;
  endAt: string;
  startTimeAt: string;
  description: string;
  frequency: number;
  quarters: number;
  addonIds: string;
  fixedPrice: number;
  calculatedPrice: number;
}

interface EditUnassignSubscriptionPayload {
  type?: "private" | "company";
  userId?: number;
  serviceId?: number;
  addonIds: number[];
  description: string;
  isFixed: boolean;
  frequency: number;
  startAt: string;
  endAt: string | null;
  fixedPrice?: number;
  cleaningDetail?: CleaningDetailPayload;
  laundryDetail?: LaundryDetailPayload;
}

export interface EditModalProps {
  isOpen: boolean;
  onClose: () => void;
  data?: UnassignSubscription;
}

const EditModal = ({ data, isOpen, onClose }: EditModalProps) => {
  const { t } = useTranslation();
  const [isConfirmationModalOpen, setIsConfirmationModalOpen] = useState(false);
  const { frequencies, teams } =
    usePage<PageProps<UnassignSubscriptionPageProps>>().props;

  const { errors: serverErrors } = usePage().props;
  const {
    register,
    reset,
    watch,
    setValue,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [collidedSchedules, setCollidedSchedules] = useState<Schedule[]>([]);

  const teamId = watch("teamId");
  const frequency = watch("frequency");
  const startAt = watch("startAt");
  const startTimeAt = watch("startTimeAt");
  const addonIds = watch("addonIds");
  const quarters = watch("quarters");

  const services = useGetServices({
    request: {
      size: -1,
      show: "active",
      include: ["addons", "quarters"],
      filter: {
        eq: {
          type: data?.customer?.membershipType ?? "",
        },
      },
      only: [
        "id",
        "name",
        "priceWithVat",
        "addons.id",
        "addons.name",
        "addons.priceWithVat",
        "addons.creditPrice",
        "quarters.minSquareMeters",
        "quarters.maxSquareMeters",
        "quarters.quarters",
      ],
    },
    query: {
      staleTime: Infinity,
    },
  });

  const fixOptions = useConst([
    { label: t("yes"), value: "true" },
    { label: t("no"), value: "false" },
  ]);

  const frequencyOptions = useConst(
    Object.entries(frequencies).map(([key, value]) => ({
      label: value,
      value: Number(key),
    })),
  );

  const teamOptions = useMemo(
    () =>
      teams?.map((item) => {
        const length = item.users?.length ?? 1;
        const label = length > 1 ? t("persons") : t("person");

        return {
          label: `${item.name} (${length} ${label})`,
          value: item.id,
        };
      }),
    [teams],
  );

  const endTimeAt = useMemo(() => {
    if (!teamId || quarters === undefined || !startTimeAt) {
      return "";
    }

    const team = teams?.find((team) => team.id === teamId);
    const calendarQuarters = Math.ceil(quarters / (team?.users?.length ?? 1));
    const startTime = toDayjs(
      toDayjs().format(`YYYY-MM-DDT${startTimeAt}:00Z`),
    );

    return startTime
      .add(calendarQuarters * 15, "minute")
      .format(SIMPLE_TIME_FORMAT);
  }, [teams, teamId, startTimeAt, quarters]);

  const addonOptions = useMemo(
    () =>
      services?.data
        ?.find((item) => item.id === data?.serviceId)
        ?.addons?.reduce(
          (acc, item) => {
            // exclude laundry add on
            if (item.id !== 1) {
              acc.push({
                label: `${item.name} (SEK ${item.priceWithVat})`,
                value: item.id,
              });
            }
            return acc;
          },
          [] as { label: string; value: number }[],
        ) ?? [],

    [services.data, data?.serviceId],
  );

  const teamName = useMemo(() => {
    if (teamId && teams) {
      const team = teams.find((team) => team.id === teamId);

      return team?.name;
    }
  }, [teamId, teams]);

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);

    const {
      teamId,
      isFixed,
      startAt,
      endAt,
      startTimeAt,
      description,
      frequency,
      quarters,
      addonIds,
      fixedPrice,
      calculatedPrice,
    } = values;

    const [year, month, date] = startAt.split("-").map(Number);
    const [startHour, startMinute] = startTimeAt.split(":").map(Number);
    const utcStartAt = toUTC({
      year,
      month: month - 1,
      date,
      hour: startHour,
      minute: startMinute,
    });

    const payload: EditUnassignSubscriptionPayload = {
      type: data?.customer?.membershipType,
      userId: data?.user?.id,
      serviceId: data?.serviceId,
      addonIds: addonIds ? JSON.parse(addonIds) : [],
      description,
      isFixed: isFixed === "true",
      frequency,
      startAt: utcStartAt.format(DATE_FORMAT),
      endAt: frequency === 0 ? null : endAt,
      fixedPrice: calculatedPrice !== fixedPrice ? fixedPrice : undefined,
      cleaningDetail: {
        propertyId: data?.cleaningDetail?.propertyId,
        quarters,
        startTime: utcStartAt.format(TIME_FORMAT),
        ...(teamId && { teamId }),
      },
    };

    const url = teamId
      ? `/unassign-subscriptions/${data?.id}/generate`
      : `/unassign-subscriptions/${data?.id}`;

    router.post(
      url,
      {
        ...(teamId && {
          _method: "PATCH",
        }),
        ...payload,
      },
      {
        onFinish: () => {
          onConfirmationModalClose();
          setIsSubmitting(false);
        },
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

  const onConfirmationModalClose = () => {
    setIsConfirmationModalOpen(false);
  };

  const onSubmit = () => {
    if (watch("teamId")) {
      setIsConfirmationModalOpen(true);
    } else {
      handleSubmit();
    }
  };

  useEffect(() => {
    if (frequency === 0 && startAt) {
      const endAt = toDayjs(startAt, false).add(
        endTimeAt && startTimeAt > endTimeAt ? 1 : 0,
        "day",
      );
      setValue("endAt", endAt.format(DATE_FORMAT));
    }
  }, [frequency, startAt, startTimeAt, endTimeAt]);

  // make sure the use effect before setting fixedPrice value
  useEffect(() => {
    reset({
      isFixed: data?.isFixed ? "true" : "false",
      frequency: data?.frequency,
      quarters: data?.cleaningDetail?.quarters,
      startAt: toDayjs(data?.startAt).format(DATE_FORMAT),
      endAt: data?.endAt
        ? toDayjs(data?.endAt, false).format(DATE_FORMAT)
        : undefined,
      startTimeAt: toDayjs(data?.startTime).format(SIMPLE_TIME_FORMAT),
      description: data?.description,
      addonIds: JSON.stringify(data?.addons?.map((addon) => addon.id) ?? []),
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data]);

  // set total price initial value and update when addonIds change
  useEffect(() => {
    if (!data?.fixedPrice) {
      const service = services.data?.find(
        (item) => item.id === data?.serviceId,
      );

      if (!service) {
        return;
      }

      const parsedAddonIds = addonIds ? JSON.parse(addonIds) : [];

      let fixedPrice = data?.totalRawPrice ?? 0;

      for (const addon of service.addons ?? []) {
        if (parsedAddonIds && !parsedAddonIds.includes(addon.id)) {
          continue;
        }

        fixedPrice += addon.priceWithVat;
      }

      fixedPrice = isNaN(fixedPrice) ? 0 : fixedPrice;
      setValue("calculatedPrice", fixedPrice);

      setValue("fixedPrice", fixedPrice);
    } else {
      setValue("calculatedPrice", data?.totalRawPrice ?? 0);

      setValue("fixedPrice", data?.totalRawPrice ?? 0);
    }
  }, [data, addonIds]);

  return (
    <>
      <Modal
        title={t("edit unassign subscription")}
        isOpen={isOpen}
        onClose={onClose}
      >
        <Flex
          as="form"
          direction="column"
          gap={4}
          onSubmit={(e) => {
            e.preventDefault();
            onSubmit();
          }}
          autoComplete="off"
          noValidate
        >
          <Autocomplete
            options={teamOptions}
            labelText={t("team")}
            errorText={
              errors.teamId?.message || serverErrors["cleaningDetail.teamId"]
            }
            value={watch("teamId")}
            {...register("teamId", {
              valueAsNumber: true,
            })}
          />
          <Autocomplete
            options={fixOptions}
            labelText={t("fixed time")}
            errorText={errors.isFixed?.message || serverErrors.isFixed}
            value={watch("isFixed")}
            {...register("isFixed", {
              required: t("validation field required"),
            })}
            isRequired
          />
          <Flex gap={4}>
            <Autocomplete
              options={frequencyOptions}
              labelText={t("frequency")}
              errorText={errors.frequency?.message || serverErrors.frequency}
              value={watch("frequency")}
              {...register("frequency", {
                required: t("validation field required"),
                valueAsNumber: true,
              })}
              isRequired
            />
            <Input
              labelText={t("total quarters")}
              type="number"
              min={1}
              errorText={
                errors.quarters?.message ||
                serverErrors["cleaningDetail.quarters"]
              }
              {...register("quarters", {
                required: t("validation field required"),
                min: {
                  value: 1,
                  message: t("validation field min", { min: 1 }),
                },
                valueAsNumber: true,
              })}
              isRequired
            />
          </Flex>
          <Flex gap={4}>
            <Input
              type="date"
              labelText={t("date start")}
              helperText={t("unassign subscription date start helper text")}
              errorText={errors.startAt?.message || serverErrors.startAt}
              {...register("startAt", {
                required: t("validation field required"),
              })}
              isRequired
            />
            <Input
              type="date"
              labelText={t("date end")}
              helperText={t("unassign subscription date end helper text")}
              errorText={errors.endAt?.message || serverErrors.endAt}
              {...register("endAt")}
              isReadOnly={frequency === 0}
            />
          </Flex>
          <Flex gap={4}>
            <TimePicker
              labelText={t("time start")}
              helperText={t("unassign subscription time start helper text")}
              errorText={
                errors.startTimeAt?.message ||
                serverErrors["cleaningDetail.startTime"]
              }
              value={startTimeAt}
              {...register("startTimeAt", {
                required: t("validation field required"),
              })}
              isRequired
            />
            <TimePicker
              labelText={t("time end")}
              helperText={t("unassign subscription time end helper text")}
              value={endTimeAt}
              isReadOnly
            />
          </Flex>
          <Autocomplete
            options={addonOptions}
            labelText={t("add ons")}
            errorText={errors.addonIds?.message || serverErrors.addonIds}
            value={watch("addonIds")}
            {...register("addonIds")}
            multiple
          />
          <Input
            as={Textarea}
            labelText={t("description")}
            errorText={errors.description?.message || serverErrors.description}
            resize="none"
            {...register("description")}
          />
          <Input
            type="number"
            labelText={t("total price")}
            min={1}
            errorText={errors.fixedPrice?.message || serverErrors.fixedPrice}
            {...register("fixedPrice", {
              required: t("validation field required"),
              min: { value: 1, message: t("validation field min", { min: 1 }) },
              valueAsNumber: true,
            })}
            isRequired
          />
          <Flex justify="right" mt={4} gap={4}>
            <Button colorScheme="gray" fontSize="sm" onClick={onClose}>
              {t("cancel")}
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
      </Modal>
      <AlertDialog
        title={t("activate subscription")}
        confirmButton={{
          isLoading: isSubmitting,
          loadingText: t("please wait"),
        }}
        confirmText={t("activate")}
        isOpen={isConfirmationModalOpen}
        onClose={onConfirmationModalClose}
        onConfirm={handleSubmit}
      >
        <Trans
          i18nKey="activate subscription alert body"
          values={{
            team: teamName,
          }}
        />
      </AlertDialog>
      <ScheduleCollisionModal
        isOpen={collidedSchedules.length > 0}
        onClose={() => setCollidedSchedules([])}
        data={collidedSchedules}
      />
    </>
  );
};

export default EditModal;
