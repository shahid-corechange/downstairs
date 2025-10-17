import {
  Card,
  CardBody,
  CardHeader,
  Flex,
  Heading,
  useConst,
} from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useEffect, useMemo } from "react";
import { useFormContext } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";

import Customer from "@/types/customer";
import LaundryPreference from "@/types/laundryPreference";

import { toDayjs } from "@/utils/datetime";

import { PageProps } from "@/types";

import { CheckoutFormType } from "../../types";
import { useDeliverySchedule, usePickupSchedule } from "./hooks/schedule";

interface OrderInfoProps {
  customer?: Customer;
  laundryPreferences?: LaundryPreference[];
  hasFixedPrice?: boolean;
  addLaundryPreferenceToCart: (laundryPreference: LaundryPreference) => void;
  removeLaundryPreferenceFromCart: () => void;
}

const OrderInfo = ({
  customer,
  laundryPreferences,
  hasFixedPrice,
  addLaundryPreferenceToCart,
  removeLaundryPreferenceFromCart,
}: OrderInfoProps) => {
  const { t } = useTranslation();
  const {
    user,
    stores,
    storeId,
    errors: serverErrors,
  } = usePage<PageProps>().props;

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
    setValue,
    watch,
    formState: { errors },
  } = useFormContext<CheckoutFormType>();

  const laundryPreferenceId = watch("laundryPreferenceId");
  const pickupScheduleId = watch("pickupScheduleId");
  const deliveryScheduleId = watch("deliveryScheduleId");
  const store = stores?.find((store) => store.id === storeId)?.name;

  const laundryPreference = useMemo(
    () =>
      laundryPreferences?.find((lp) => lp.id === Number(laundryPreferenceId)),
    [laundryPreferences, laundryPreferenceId],
  );

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

  const {
    minDeliveryAt,
    pickupScheduleOptions,
    pickupSchedules,
    isValidPickupSchedule,
  } = usePickupSchedule({
    customerId: customer?.id,
    laundryPreference,
    pickupScheduleId,
  });

  const {
    deliveryScheduleOptions,
    deliverySchedules,
    isValidDeliverySchedule,
  } = useDeliverySchedule({
    customerId: customer?.id,
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

  useEffect(() => {
    if (!laundryPreference) {
      removeLaundryPreferenceFromCart();
      return;
    }

    addLaundryPreferenceToCart(laundryPreference);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [laundryPreference]);

  useEffect(() => {
    if (pickupScheduleId && !isValidPickupSchedule) {
      setValue("pickupScheduleId", undefined);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pickupScheduleOptions, pickupScheduleId]);

  useEffect(() => {
    if (deliveryScheduleId && !isValidDeliverySchedule) {
      setValue("deliveryScheduleId", undefined);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [deliveryScheduleOptions, deliveryScheduleId]);

  return (
    <Card>
      <CardHeader>
        <Heading size="sm">{t("order info")}</Heading>
      </CardHeader>
      <CardBody fontSize="sm">
        <Flex direction="column" gap={4} justifyContent="space-between">
          <Autocomplete
            labelText={t("laundry preference")}
            {...formFieldProps}
            options={laundryPreferenceOptions}
            value={watch("laundryPreferenceId")}
            errorText={
              errors.laundryPreferenceId?.message ||
              serverErrors["laundryPreferenceId"]
            }
            {...register("laundryPreferenceId", {
              required: t("validation field required"),
            })}
            isRequired
          />

          <Autocomplete
            labelText={t("pickup schedule")}
            {...formFieldProps}
            options={pickupScheduleOptions}
            value={pickupScheduleId}
            isLoading={pickupSchedules.isFetching}
            errorText={
              errors.pickupScheduleId?.message ||
              serverErrors["pickupScheduleId"]
            }
            {...register("pickupScheduleId", {
              valueAsNumber: true,
            })}
            allowEmpty
          />

          <Autocomplete
            labelText={t("delivery schedule")}
            {...formFieldProps}
            options={deliveryScheduleOptions}
            value={watch("deliveryScheduleId")}
            isLoading={deliverySchedules.isFetching}
            errorText={
              errors.deliveryScheduleId?.message ||
              serverErrors["deliveryScheduleId"]
            }
            {...register("deliveryScheduleId", {
              valueAsNumber: true,
            })}
            allowEmpty
          />

          <Input
            labelText={t("delivery at")}
            {...formFieldProps}
            value={deliveryAt}
            isReadOnly
          />

          <Input
            labelText={t("store")}
            {...formFieldProps}
            value={store || "-"}
            isReadOnly
          />

          <Input
            labelText={t("sales")}
            {...formFieldProps}
            value={user?.fullname || "-"}
            isReadOnly
          />
        </Flex>
      </CardBody>
    </Card>
  );
};

export default OrderInfo;
