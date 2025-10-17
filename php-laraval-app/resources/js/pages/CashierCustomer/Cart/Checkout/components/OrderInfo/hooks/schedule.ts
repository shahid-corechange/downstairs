import { useConst } from "@chakra-ui/react";
import { Dayjs } from "dayjs";
import { useMemo } from "react";

import { useGetCustomerSchedules } from "@/services/cashierOrder";

import LaundryPreference from "@/types/laundryPreference";

import { addBusinessDays, toDayjs } from "@/utils/datetime";

interface UsePickupScheduleProps {
  customerId?: number;
  pickupScheduleId?: number;
  laundryPreference?: LaundryPreference;
}

interface UseDeliveryScheduleProps {
  customerId?: number;
  minDeliveryAt?: Dayjs;
  deliveryScheduleId?: number;
}

interface CustomerScheduleConfigProps {
  customerId?: number;
  minStartAt?: string;
  enabled?: boolean;
}

const customerScheduleConfig = ({
  customerId,
  minStartAt,
  enabled,
}: CustomerScheduleConfigProps) => ({
  request: {
    include: ["property.address", "team"],
    only: [
      "id",
      "customerId",
      "startAt",
      "endAt",
      "property.address.fullAddress",
      "team.name",
    ],
    filter: {
      eq: {
        "status": "booked",
        "customerId": customerId,
        "detail.laundryOrderId": "null",
      },
      ...(minStartAt ? { gte: { startAt: minStartAt } } : {}),
    },
    sort: { startAt: "asc" as const },
    size: -1,
  },
  query: {
    enabled: enabled,
    keepPreviousData: true,
  },
});

export const usePickupSchedule = ({
  pickupScheduleId,
  customerId,
  laundryPreference,
}: UsePickupScheduleProps) => {
  const startAt = useConst(toDayjs().toISOString());

  const pickupSchedules = useGetCustomerSchedules({
    ...customerScheduleConfig({
      customerId,
      minStartAt: startAt,
      enabled: !!customerId,
    }),
  });

  const pickupScheduleOptions = useMemo(
    () =>
      pickupSchedules.data?.data.map((schedule) => {
        const date = toDayjs(schedule.startAt).format("LL");
        const startAt = toDayjs(schedule.startAt).format("HH:mm");
        const endAt = toDayjs(schedule.endAt).format("HH:mm");
        const address = schedule.property?.address?.fullAddress;
        const team = schedule.team?.name;

        return {
          label: `${date} | ${startAt} - ${endAt} | ${address} | ${team}`,
          value: schedule.id,
        };
      }) ?? [],
    [pickupSchedules.data?.data],
  );

  const minDeliveryAt = useMemo(() => {
    const pickupSchedule = pickupSchedules.data?.data.find(
      (schedule) => schedule.id === Number(pickupScheduleId),
    );

    const startAt = pickupSchedule
      ? toDayjs(pickupSchedule.startAt)
      : toDayjs();

    return addBusinessDays(startAt, laundryPreference?.hours ?? 0);
  }, [pickupSchedules.data?.data, laundryPreference?.hours, pickupScheduleId]);

  const isValidPickupSchedule = useMemo(
    () =>
      pickupScheduleOptions.some(
        (option) => option.value === Number(pickupScheduleId),
      ),
    [pickupScheduleOptions, pickupScheduleId],
  );

  return {
    minDeliveryAt,
    pickupSchedules,
    pickupScheduleOptions,
    isValidPickupSchedule,
  };
};

export const useDeliverySchedule = ({
  customerId,
  minDeliveryAt,
  deliveryScheduleId,
}: UseDeliveryScheduleProps) => {
  const deliverySchedules = useGetCustomerSchedules({
    ...customerScheduleConfig({
      customerId,
      minStartAt: minDeliveryAt?.toISOString(),
      enabled: !!customerId && !!minDeliveryAt,
    }),
  });

  const deliveryScheduleOptions = useMemo(
    () =>
      deliverySchedules.data?.data?.map((schedule) => {
        const date = toDayjs(schedule.startAt).format("LL");
        const startAt = toDayjs(schedule.startAt).format("HH:mm");
        const endAt = toDayjs(schedule.endAt).format("HH:mm");
        const address = schedule.property?.address?.fullAddress;
        const team = schedule.team?.name;

        return {
          label: `${date} | ${startAt} - ${endAt} | ${address} | ${team}`,
          value: schedule.id,
        };
      }) ?? [],
    [deliverySchedules.data?.data],
  );

  const isValidDeliverySchedule = useMemo(
    () =>
      deliveryScheduleOptions.some(
        (option) => option.value === Number(deliveryScheduleId),
      ),
    [deliveryScheduleOptions, deliveryScheduleId],
  );

  return {
    deliverySchedules,
    deliveryScheduleOptions,
    isValidDeliverySchedule,
  };
};
