import {
  Card,
  CardBody,
  CardHeader,
  Flex,
  Heading,
  useConst,
} from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import dayjs from "dayjs";
import { useEffect, useMemo } from "react";
import { useFormContext } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import Autocomplete from "@/components/Autocomplete";

import { LaundryOrder } from "@/types/laundryOrder";
import LaundryPreference from "@/types/laundryPreference";
import Schedule from "@/types/schedule";

import { toDayjs } from "@/utils/datetime";

import { PageProps } from "@/types";

import { CheckoutFormType } from "../../types";
import InfoRow from "./InfoRow";
import { useDeliverySchedule, usePickupSchedule } from "./hooks/schedule";

interface OrderInfoProps {
  laundryOrder: LaundryOrder;
  laundryPreferences: LaundryPreference[];
  hasFixedPrice?: boolean;
  isSubmitting: boolean;
  addLaundryPreferenceToCart: (laundryPreference: LaundryPreference) => void;
  removeLaundryPreferenceFromCart: () => void;
}

const OrderInfo = ({
  laundryOrder,
  laundryPreferences,
  hasFixedPrice,
  isSubmitting,
  addLaundryPreferenceToCart,
  removeLaundryPreferenceFromCart,
}: OrderInfoProps) => {
  const { t } = useTranslation();
  const { user, errors: serverErrors } = usePage<PageProps>().props;

  const formFieldProps = useConst(() => ({
    container: {
      display: "grid",
      gridTemplateColumns: "1fr 2fr",
      alignItems: "center",
      rowGap: 1,
      columnGap: 4,
    },
    error: {
      gridColumn: "2",
      gridRow: "2",
    },
    label: {
      m: 0,
    },
  }));

  const {
    register,
    watch,
    formState: { errors },
  } = useFormContext<CheckoutFormType>();

  const laundryPreferenceId = watch("laundryPreferenceId");
  const pickupScheduleId = watch("pickupScheduleId");
  const deliveryScheduleId = watch("deliveryScheduleId");

  const laundryPreferenceOptions = useMemo(
    () =>
      laundryPreferences
        ?.filter((lp) => (hasFixedPrice ? lp.id === 1 : true))
        ?.map((laundryPreference) => ({
          label: laundryPreference.name,
          value: laundryPreference.id,
        })) ?? [],
    [laundryPreferences, hasFixedPrice],
  );

  const laundryPreference = useMemo(
    () =>
      laundryPreferences?.find((lp) => lp.id === Number(laundryPreferenceId)),
    [laundryPreferences, laundryPreferenceId],
  );

  const { minDeliveryAt, pickupScheduleOptions, pickupSchedules } =
    usePickupSchedule({
      laundryOrder,
      laundryPreference,
      pickupScheduleId,
    });

  const { deliveryScheduleOptions, deliverySchedules } = useDeliverySchedule({
    laundryOrder,
    minDeliveryAt,
    deliveryScheduleId,
  });

  const deliveryAt = useMemo(() => {
    if (deliveryScheduleId) {
      const deliverySchedule = deliverySchedules.data?.data.find(
        (schedule) => schedule.id === Number(deliveryScheduleId),
      );

      return toDayjs(deliverySchedule?.startAt).format("LLLL");
    }

    return toDayjs(minDeliveryAt).format("LLLL");
  }, [deliverySchedules.data?.data, deliveryScheduleId, minDeliveryAt]);

  const scheduleFormatter = (schedule: Schedule) => {
    const date = toDayjs(schedule?.startAt).format("LL");
    const startAt = toDayjs(schedule?.startAt).format("HH:mm");
    const endAt = toDayjs(schedule?.endAt).format("HH:mm");
    const address = schedule?.property?.address?.fullAddress;
    const team = schedule.team?.name;

    return `${date} | ${startAt} - ${endAt} | ${address} | ${team}`;
  };

  useEffect(() => {
    if (!laundryPreference) {
      removeLaundryPreferenceFromCart();
      return;
    }

    addLaundryPreferenceToCart(laundryPreference);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [laundryPreference]);

  return (
    <Card>
      <CardHeader>
        <Heading size="sm">{t("order info")}</Heading>
      </CardHeader>
      <CardBody fontSize="sm">
        <Flex direction="column" gap={4}>
          {laundryOrder.status === "pending" && (
            <Alert
              status="info"
              title={t("info")}
              message={t("change schedule information")}
              fontSize="small"
            />
          )}

          {laundryOrder.status === "pending" ? (
            <>
              <Autocomplete
                labelText={t("laundry preference")}
                {...formFieldProps}
                options={laundryPreferenceOptions}
                value={laundryPreferenceId}
                isDisabled={isSubmitting}
                errorText={
                  errors.laundryPreferenceId?.message ||
                  serverErrors["laundryPreferenceId"]
                }
                {...register("laundryPreferenceId", {
                  valueAsNumber: true,
                  required: t("validation field required"),
                })}
                isRequired
              />
              <Autocomplete
                labelText={t("pickup schedule")}
                {...formFieldProps}
                errorText={
                  errors.laundryPreferenceId?.message ||
                  serverErrors["laundryPreferenceId"]
                }
                options={pickupScheduleOptions}
                value={pickupScheduleId}
                isLoading={pickupSchedules.isFetching}
                isDisabled={isSubmitting}
                {...register("pickupScheduleId", {
                  valueAsNumber: true,
                })}
                allowEmpty
              />
            </>
          ) : (
            <>
              <InfoRow
                label={t("laundry preference")}
                value={laundryOrder?.laundryPreference?.name}
              />
              <InfoRow
                label={t("pickup schedule")}
                value={
                  laundryOrder?.pickupInCleaning
                    ? scheduleFormatter(laundryOrder?.pickupInCleaning)
                    : "-"
                }
              />
            </>
          )}
          {[
            "pending",
            "in_progress_pickup",
            "picked_up",
            "in_progress_store",
            "in_progress_laundry",
          ].includes(laundryOrder.status) ? (
            <Autocomplete
              labelText={t("delivery schedule")}
              {...formFieldProps}
              errorText={
                errors.deliveryScheduleId?.message ||
                serverErrors["deliveryScheduleId"]
              }
              options={deliveryScheduleOptions}
              value={watch("deliveryScheduleId")}
              isLoading={deliverySchedules.isFetching}
              isDisabled={isSubmitting}
              {...register("deliveryScheduleId", {
                valueAsNumber: true,
              })}
              allowEmpty
            />
          ) : (
            <InfoRow
              label={t("delivery schedule")}
              value={
                laundryOrder?.deliveryInCleaning
                  ? scheduleFormatter(laundryOrder?.deliveryInCleaning)
                  : "-"
              }
            />
          )}
          <InfoRow label={t("delivery at")} value={deliveryAt} />
          <InfoRow label={t("status")} value={t(laundryOrder?.status)} />
          <InfoRow
            label={t("paid")}
            value={
              laundryOrder?.paidAt
                ? dayjs(laundryOrder?.paidAt).format("LLL")
                : "-"
            }
          />
          <InfoRow
            label={t("payment method")}
            value={
              laundryOrder?.paymentMethod ? t(laundryOrder?.paymentMethod) : "-"
            }
          />
          <InfoRow label={t("store")} value={laundryOrder?.store?.name} />
          <InfoRow label={t("sales")} value={user?.fullname} />
        </Flex>
      </CardBody>
    </Card>
  );
};

export default OrderInfo;
