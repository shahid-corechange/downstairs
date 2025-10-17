import { Button, Flex } from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { router, usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import Autocomplete from "@/components/Autocomplete";
import { AutocompleteOption } from "@/components/Autocomplete/types";
import Modal from "@/components/Modal";

import { useGetCashierFixedPrice } from "@/services/cashierFixedPrice";

import { LaundryOrder } from "@/types/laundryOrder";
import LaundryPreference from "@/types/laundryPreference";

import { PageProps } from "@/types";

import { useDeliverySchedule, usePickupSchedule } from "./hooks/schedule";

type FormValues = {
  userId: number;
  laundryPreferenceId: number;
  pickupScheduleId?: number;
  deliveryScheduleId?: number;
};

export type ChangeScheduleModalData = {
  laundryOrder?: LaundryOrder;
  laundryPreferences?: LaundryPreference[];
};

interface ChangeScheduleModalProps {
  data?: ChangeScheduleModalData;
  isOpen: boolean;
  onClose: () => void;
  onRefetch: () => void;
}

const ChangeScheduleModal = ({
  data,
  isOpen,
  onClose,
  onRefetch,
}: ChangeScheduleModalProps) => {
  const { t } = useTranslation();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const { errors: serverErrors } = usePage().props;

  const {
    register,
    watch,
    reset,
    setValue,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();

  const laundryPreferenceId = watch("laundryPreferenceId");
  const pickupScheduleId = watch("pickupScheduleId");
  const deliveryScheduleId = watch("deliveryScheduleId");

  const { data: fixedPrice } = useGetCashierFixedPrice(
    data?.laundryOrder?.userId,
    {
      request: {
        include: ["laundryProducts"],
        only: ["id"],
      },
      query: {
        enabled: isOpen && !!data?.laundryOrder?.userId,
      },
    },
  );

  const orderHasFixedPrice = useMemo(() => {
    const fixedPriceProducts = fixedPrice?.data?.laundryProducts;

    if (!fixedPriceProducts) {
      return false;
    }

    if (fixedPriceProducts.length === 0) {
      return true;
    }

    const fixedPriceProductIds = new Set(fixedPriceProducts.map((fp) => fp.id));

    return data?.laundryOrder?.products?.some((lp) =>
      fixedPriceProductIds.has(lp.productId),
    );
  }, [data?.laundryOrder?.products, fixedPrice?.data?.laundryProducts]);

  const laundryPreferenceOptions = useMemo(
    () =>
      data?.laundryPreferences?.reduce((acc, laundryPreference) => {
        if (orderHasFixedPrice ? laundryPreference.id === 1 : true) {
          acc.push({
            label: laundryPreference.name,
            value: laundryPreference.id,
          });
        }
        return acc;
      }, [] as AutocompleteOption[]) ?? [],
    [data?.laundryPreferences, orderHasFixedPrice],
  );

  const laundryPreference = useMemo(
    () =>
      data?.laundryPreferences?.find(
        (lp) => lp.id === Number(laundryPreferenceId),
      ),
    [data?.laundryPreferences, laundryPreferenceId],
  );

  const {
    minDeliveryAt,
    pickupScheduleOptions,
    pickupSchedules,
    isValidPickupSchedule,
  } = usePickupSchedule({
    laundryOrder: data?.laundryOrder,
    laundryPreference,
    pickupScheduleId,
  });

  const {
    deliveryScheduleOptions,
    deliverySchedules,
    isValidDeliverySchedule,
  } = useDeliverySchedule({
    laundryOrder: data?.laundryOrder,
    minDeliveryAt,
    deliveryScheduleId,
  });

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);

    router.patch(
      `/cashier/orders/${data?.laundryOrder?.id}`,
      {
        userId: data?.laundryOrder?.userId,
        sendMessage: false,
        laundryPreferenceId: values?.laundryPreferenceId,
        pickupScheduleId: values?.pickupScheduleId,
        deliveryScheduleId: values?.deliveryScheduleId,
      },
      {
        onFinish: () => setIsSubmitting(false),
        onSuccess: (page) => {
          const {
            flash: { error },
          } = (page as Page<PageProps>).props;

          if (error) {
            return;
          }

          onClose();
          onRefetch();
        },
      },
    );
  });

  useEffect(() => {
    if (!isValidPickupSchedule) {
      setValue("pickupScheduleId", undefined);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isValidPickupSchedule]);

  useEffect(() => {
    if (!isValidDeliverySchedule) {
      setValue("deliveryScheduleId", undefined);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isValidDeliverySchedule]);

  useEffect(() => {
    if (isOpen && data?.laundryOrder) {
      reset({
        laundryPreferenceId: data?.laundryOrder?.laundryPreferenceId,
        pickupScheduleId: data?.laundryOrder?.pickupInCleaningId,
        deliveryScheduleId: data?.laundryOrder?.deliveryInCleaningId,
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isOpen, data?.laundryOrder]);

  return (
    <Modal
      title={t("change schedule")}
      size="3xl"
      isOpen={isOpen}
      onClose={onClose}
    >
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
        <Alert
          status="info"
          title={t("info")}
          message={t("change schedule information")}
          fontSize="small"
        />

        <Autocomplete
          labelText={t("laundry preference")}
          options={laundryPreferenceOptions}
          value={laundryPreferenceId}
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
          options={pickupScheduleOptions}
          labelText={t("pickup schedule")}
          errorText={
            errors.pickupScheduleId?.message || serverErrors.pickupScheduleId
          }
          value={pickupScheduleId}
          isLoading={pickupSchedules.isFetching}
          {...register("pickupScheduleId", {
            valueAsNumber: true,
          })}
          allowEmpty
        />
        <Autocomplete
          labelText={t("delivery schedule")}
          errorText={
            errors.deliveryScheduleId?.message ||
            serverErrors["deliveryScheduleId"]
          }
          options={deliveryScheduleOptions}
          value={deliveryScheduleId}
          isLoading={deliverySchedules.isFetching}
          {...register("deliveryScheduleId", {
            valueAsNumber: true,
          })}
          allowEmpty
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
            {t("save")}
          </Button>
        </Flex>
      </Flex>
    </Modal>
  );
};
export default ChangeScheduleModal;
