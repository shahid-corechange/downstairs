import { useConst } from "@chakra-ui/react";
import { Dayjs } from "dayjs";
import { useMemo } from "react";

import { useGetCustomerSchedules } from "@/services/cashierOrder";

import { LaundryOrder } from "@/types/laundryOrder";
import LaundryPreference from "@/types/laundryPreference";

import { addBusinessDays, toDayjs } from "@/utils/datetime";

interface UsePickupScheduleProps {
  laundryOrder?: LaundryOrder;
  laundryPreference?: LaundryPreference;
  pickupScheduleId?: number;
}

interface UseDeliveryScheduleProps {
  laundryOrder?: LaundryOrder;
  minDeliveryAt?: Dayjs;
  deliveryScheduleId?: number;
}

interface CustomerScheduleConfigProps {
  customerId?: number;
  laundryOrderId?: number;
  minStartAt?: string;
  enabled?: boolean;
}

const customerScheduleConfig = ({
  customerId,
  laundryOrderId,
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
        status: "booked",
        customerId: customerId,
      },
      ...(minStartAt ? { gte: { startAt: minStartAt } } : {}),
    },
    orFilters: [
      {
        eq: {
          "detail.laundryOrderId": laundryOrderId,
        },
        nullable: {
          "detail.laundryOrderId": true,
        },
      },
    ],
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
  laundryOrder,
  laundryPreference,
}: UsePickupScheduleProps) => {
  const startAt = useConst(toDayjs().toISOString());

  const pickupSchedules = useGetCustomerSchedules({
    ...customerScheduleConfig({
      customerId: laundryOrder?.customerId,
      laundryOrderId: laundryOrder?.id,
      minStartAt: startAt,
      enabled: !!laundryOrder?.customerId && !!laundryOrder?.orderedAt,
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

    return pickupSchedule
      ? addBusinessDays(
          toDayjs(pickupSchedule.startAt),
          laundryPreference?.hours ?? 0,
        )
      : toDayjs(laundryOrder?.dueAt);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pickupSchedules.data?.data, laundryPreference?.hours, pickupScheduleId]);

  return {
    minDeliveryAt,
    pickupSchedules,
    pickupScheduleOptions,
  };
};

export const useDeliverySchedule = ({
  laundryOrder,
  minDeliveryAt,
}: UseDeliveryScheduleProps) => {
  const deliverySchedules = useGetCustomerSchedules({
    ...customerScheduleConfig({
      customerId: laundryOrder?.customerId,
      laundryOrderId: laundryOrder?.id,
      minStartAt: minDeliveryAt?.toISOString(),
      enabled: !!laundryOrder?.customerId && !!minDeliveryAt,
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

  return {
    deliverySchedules,
    deliveryScheduleOptions,
  };
};
