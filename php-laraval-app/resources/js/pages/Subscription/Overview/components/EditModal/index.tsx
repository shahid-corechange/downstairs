import {
  Button,
  Flex,
  Link,
  Textarea,
  useConst,
  useDisclosure,
} from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
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

import FixedPrice from "@/types/fixedPrice";
import Schedule from "@/types/schedule";
import Service from "@/types/service";
import Subscription from "@/types/subscription";
import SubscriptionCleaningDetail from "@/types/subscriptionCleaningDetail";
import Team from "@/types/team";

import { toDayjs, toUTC } from "@/utils/datetime";
import { createQueryString } from "@/utils/request";

import { PageProps } from "@/types";

import UpdateLaundryConfirmation from "./components/UpdateLaundryConfirmation";

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
  totalPrice: number;
  calculatedPrice: number;
}

interface EditSubscriptionPayload {
  addonIds: number[];
  description: string;
  isFixed: boolean;
  frequency: number;
  totalPrice?: number;
  startAt: string;
  endAt: string | null;
  // products?: Product[];
  cleaningDetail?: CleaningDetailPayload;
  laundryDetail?: LaundryDetailPayload;
}

type CleaningDetailPayload = {
  teamId?: number;
  quarters?: number;
  startTime?: string;
  endTime?: string;
};

type LaundryDetailPayload = {
  laundryPreferenceId?: number;
  pickupPropertyId?: number;
  pickupTeamId?: number;
  pickupTime?: string;
  deliveryPropertyId?: number;
  deliveryTeamId?: number;
  deliveryTime?: string;
};

export interface EditModalProps {
  isOpen: boolean;
  frequencies: Record<string, string>;
  teams: Team[];
  services: Service[];
  onClose: () => void;
  data?: Subscription;
}

const EditModal = ({
  data,
  frequencies,
  services,
  teams,
  isOpen,
  onClose,
}: EditModalProps) => {
  const { t } = useTranslation();
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
  const [isStartChange, setIsStartChange] = useState(false);

  const {
    isOpen: isAlertOpen,
    onOpen: onAlertOpen,
    onClose: onAlertClose,
  } = useDisclosure();

  const {
    isOpen: isUpdateLaundryConfirmationOpen,
    onOpen: onUpdateLaundryConfirmationOpen,
    onClose: onUpdateLaundryConfirmationClose,
  } = useDisclosure();

  const queryString = createQueryString<FixedPrice>({
    filter: { eq: { id: data?.fixedPriceId } },
  });

  const teamId = watch("teamId");
  const frequency = watch("frequency");
  const startAt = watch("startAt");
  const startTimeAt = watch("startTimeAt");
  const addonIds = watch("addonIds");
  const quarters = watch("quarters");
  const description = watch("description");

  const isShowManuallyUpdatedSchedulesAlert =
    isStartChange && (data?.updatedSchedules?.length ?? 0) > 0;
  const isShowNoteChangedAlert =
    (description ?? "") !== (data?.description ?? "");

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
      teams.map((item) => {
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
    const { endTime } = data ?? {};

    if (!teamId || quarters === undefined || !startTimeAt) {
      return endTime ?? "";
    }

    const team = teams.find((team) => team.id === teamId);
    const calendarQuarters = Math.ceil(quarters / (team?.users?.length ?? 1));
    const startTime = toDayjs(
      toDayjs().format(`YYYY-MM-DDT${startTimeAt}:00Z`),
    );

    return startTime
      .add(calendarQuarters * 15, "minute")
      .format(SIMPLE_TIME_FORMAT);
  }, [data, teams, teamId, startTimeAt, quarters]);

  const addonOptions = useMemo(
    () =>
      services
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

    [services, data?.serviceId],
  );

  const handleChangeStartTime = (value: string) => {
    setIsStartChange(
      value !== toDayjs(data?.startTime).format(SIMPLE_TIME_FORMAT),
    );
  };

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);

    const {
      startAt,
      startTimeAt,
      frequency,
      endAt,
      addonIds,
      teamId,
      isFixed,
      description,
      quarters,
      totalPrice,
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

    const payload: EditSubscriptionPayload = {
      addonIds: addonIds ? JSON.parse(addonIds) : [],
      description,
      isFixed: isFixed === "true",
      frequency,
      totalPrice: calculatedPrice !== totalPrice ? totalPrice : undefined,
      startAt: utcStartAt.format(DATE_FORMAT),
      endAt: frequency === 0 ? null : endAt,
      cleaningDetail: {
        teamId,
        quarters,
        startTime: utcStartAt.format(TIME_FORMAT),
      },
    };

    router.post(
      `/customers/subscriptions/${data?.id}`,
      { _method: "PATCH", ...payload },
      {
        onFinish: () => {
          setIsSubmitting(false);
          onAlertClose();
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

          setIsStartChange(false);
          onClose();
        },
      },
    );
  });

  useEffect(() => {
    if (frequency === 0 && startAt) {
      const endAt = toDayjs(startAt, false).add(
        startTimeAt > endTimeAt ? 1 : 0,
        "day",
      );
      setValue("endAt", endAt.format(DATE_FORMAT));
    }
  }, [frequency, startAt, startTimeAt, endTimeAt]);

  // make sure the use effect before setting totalPrice value
  useEffect(() => {
    reset({
      teamId: (data?.detail as SubscriptionCleaningDetail)?.teamId,
      isFixed: data?.isFixed ? "true" : "false",
      frequency: data?.frequency,
      quarters: data?.detail?.quarters,
      startAt: toDayjs(data?.startAt).format(DATE_FORMAT),
      endAt: data?.endAt
        ? toDayjs(data?.endAt, false).format(DATE_FORMAT)
        : undefined,
      startTimeAt: toDayjs(data?.startTime).format(SIMPLE_TIME_FORMAT),
      description: data?.description,
      addonIds: JSON.stringify((data?.addons || []).map((item) => item.id)),
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data]);

  // set total price initial value and update when addonIds change
  useEffect(() => {
    if (!data?.fixedPriceId) {
      const service = services.find((item) => item.id === data?.serviceId);

      if (!service) {
        return;
      }

      const parsedAddonIds = addonIds ? JSON.parse(addonIds) : [];

      let totalPrice = data?.totalRawPrice ?? 0;

      for (const addon of service.addons ?? []) {
        if (!parsedAddonIds.includes(addon.id)) {
          continue;
        }

        totalPrice += addon.priceWithVat;
      }

      totalPrice = isNaN(totalPrice) ? 0 : totalPrice;
      setValue("calculatedPrice", totalPrice);

      setValue("totalPrice", totalPrice);
    } else {
      setValue("calculatedPrice", data?.totalRawPrice ?? 0);

      setValue("totalPrice", data?.totalRawPrice ?? 0);
    }
  }, [data, addonIds]);

  return (
    <>
      <Modal
        title={t("edit subscription")}
        isOpen={isOpen}
        onClose={() => {
          onClose();
          setIsStartChange(false);
        }}
      >
        <Flex
          as="form"
          direction="column"
          gap={4}
          onSubmit={(e) => {
            e.preventDefault();

            // show alert if start time is changed and there are manually updated schedules or note is changed
            if (isShowManuallyUpdatedSchedulesAlert || isShowNoteChangedAlert) {
              onAlertOpen();
              return;
            }

            if (data?.isCleaningHasLaundry) {
              onUpdateLaundryConfirmationOpen();
              return;
            }

            handleSubmit();
          }}
          autoComplete="off"
          noValidate
        >
          <Autocomplete
            options={teamOptions}
            labelText={t("team")}
            errorText={errors.teamId?.message || serverErrors.teamId}
            value={watch("teamId")}
            {...register("teamId", {
              required: t("validation field required"),
              valueAsNumber: true,
            })}
            isRequired
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
              errorText={errors.quarters?.message || serverErrors.quarters}
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
              helperText={t("subscription date start helper text")}
              errorText={errors.startAt?.message || serverErrors.startAt}
              {...register("startAt", {
                required: t("validation field required"),
              })}
              isRequired
            />
            <Input
              type="date"
              labelText={t("date end")}
              helperText={t("subscription date end helper text")}
              errorText={errors.endAt?.message || serverErrors.endAt}
              {...register("endAt")}
              isReadOnly={frequency === 0}
            />
          </Flex>
          <Flex gap={4}>
            <TimePicker
              labelText={t("time start")}
              helperText={t("subscription time start helper text")}
              errorText={
                errors.startTimeAt?.message ||
                serverErrors.startTimeAt ||
                (isStartChange ? t("change subscription start time") : "")
              }
              value={startTimeAt}
              {...register("startTimeAt", {
                required: t("validation field required"),
                onChange: (e) => handleChangeStartTime(e.target.value),
              })}
              isRequired
            />
            <TimePicker
              labelText={t("time end")}
              helperText={t("subscription time end helper text")}
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
            labelText={t("note")}
            helperText={t("subscription note helper text")}
            errorText={errors.description?.message || serverErrors.description}
            resize="none"
            {...register("description")}
          />
          <Input
            type="number"
            labelText={t("total price")}
            helperText={
              data?.fixedPriceId ? (
                <Link href={`fixedprices${queryString}`} target="_blank">
                  {t("fix price applied")}
                </Link>
              ) : (
                ""
              )
            }
            helper={
              data?.fixedPriceId
                ? {
                    color: "blue.500",
                    textDecoration: "underline",
                    cursor: "pointer",
                  }
                : undefined
            }
            min={1}
            errorText={errors.totalPrice?.message || serverErrors.totalPrice}
            {...register("totalPrice", {
              required: t("validation field required"),
              min: { value: 1, message: t("validation field min", { min: 1 }) },
              valueAsNumber: true,
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
      <AlertDialog
        title={t("edit subscription")}
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
        {isShowManuallyUpdatedSchedulesAlert && (
          <Alert
            status="warning"
            title={t("warning")}
            message={t("has manually updated schedule modal alert warning")}
            fontSize="small"
            mb={6}
          />
        )}
        {isShowNoteChangedAlert && (
          <Alert
            status="warning"
            title={t("warning")}
            message={t("note changed alert warning")}
            fontSize="small"
            mb={6}
          />
        )}
        {t("subscription edit alert body")}
      </AlertDialog>
      <ScheduleCollisionModal
        isOpen={collidedSchedules.length > 0}
        onClose={() => setCollidedSchedules([])}
        data={collidedSchedules}
      />
      <UpdateLaundryConfirmation
        isOpen={isUpdateLaundryConfirmationOpen}
        onClose={onUpdateLaundryConfirmationClose}
        isLoading={isSubmitting}
        onConfirm={handleSubmit}
      />
    </>
  );
};

export default EditModal;
