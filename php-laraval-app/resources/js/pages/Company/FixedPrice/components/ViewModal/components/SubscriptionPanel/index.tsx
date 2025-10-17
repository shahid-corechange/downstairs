import {
  Button,
  Flex,
  TabPanel,
  TabPanelProps,
  useConst,
} from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { router, usePage } from "@inertiajs/react";
import { useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";
import { LuTrash } from "react-icons/lu";

import Alert from "@/components/Alert";
import AuthorizationGuard from "@/components/AuthorizationGuard";
import Autocomplete from "@/components/Autocomplete";
import DataTable from "@/components/DataTable";
import Input from "@/components/Input";

import {
  DATE_FORMAT,
  SIMPLE_TIME_FORMAT,
  WEEKDAYS,
} from "@/constants/datetime";
import { FREQUENCIES } from "@/constants/frequency";

import { useGetCompanySubscriptions } from "@/services/companySubscription";
import { getSubscriptionTypes } from "@/services/subscription";

import FixedPrice from "@/types/fixedPrice";
import Subscription from "@/types/subscription";

import { toDayjs } from "@/utils/datetime";

import { PageProps } from "@/types";

import getColumns from "./column";

interface SubscriptionPanelProps extends TabPanelProps {
  fixedPrice: FixedPrice;
  onRefetch: () => void;
}

type FormValues = {
  period: "per booking" | "monthly";
  subscriptionIds: string;
  startDate?: string;
  endDate?: string;
};

const SubscriptionPanel = ({
  fixedPrice,
  onRefetch,
  ...props
}: SubscriptionPanelProps) => {
  const { t } = useTranslation();

  const { errors: serverErrors } = usePage().props;

  const {
    register,
    watch,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    defaultValues: {
      period: fixedPrice.isPerOrder ? "per booking" : "monthly",
      startDate: fixedPrice.startDate
        ? toDayjs(fixedPrice.startDate).format(DATE_FORMAT)
        : "",
      endDate: fixedPrice.startDate
        ? toDayjs(fixedPrice.endDate).format(DATE_FORMAT)
        : "",
    },
  });

  const period = watch("period");

  const [subscriptions, setSubscriptions] = useState<Subscription[]>(
    fixedPrice?.subscriptions ?? [],
  );
  const [isSubmitting, setIsSubmitting] = useState(false);

  const columns = useConst(getColumns(t));
  const subscriptionTypes = useConst(getSubscriptionTypes(fixedPrice.type));

  const freeSubscriptions = useGetCompanySubscriptions({
    request: {
      filter: {
        eq: {
          fixedPriceId: "null",
          userId: fixedPrice.userId,
        },
        in: {
          subscribableType: subscriptionTypes,
        },
      },
      show: "active",
      include: [
        "service",
        "detail.team",
        "detail.property.address.city.country",
        "detail.pickupTeam",
        "detail.pickupProperty.address.city.country",
        "detail.deliveryTeam",
        "detail.deliveryProperty.address.city.country",
      ],
      only: [
        "id",
        "subscribableType",
        "subscribableId",
        "startTime",
        "endTime",
        "frequency",
        "weekday",
        "service.name",
        "detail.teamName",
        "detail.address",
      ],
      size: -1,
    },
  });

  const subscriptionOptions = useMemo(
    () =>
      freeSubscriptions.data
        ?.filter(
          ({ id }) => subscriptions.findIndex((item) => item.id === id) === -1,
        )
        ?.map(
          ({ id, service, frequency, weekday, detail, startTime, endTime }) => {
            const fullAddress = detail?.address ?? "-";
            const serviceName = service?.name ?? "-";
            const teamName = detail?.teamName ?? "-";
            const frequencyLabel =
              t(FREQUENCIES[frequency as keyof typeof FREQUENCIES]) ?? "-";
            const weekdayLabel = t(WEEKDAYS[weekday - 1]) ?? "-";
            const formattedStartTime =
              toDayjs(startTime).format(SIMPLE_TIME_FORMAT);
            const formattedEndTime =
              toDayjs(endTime).format(SIMPLE_TIME_FORMAT);

            return {
              label: [
                fullAddress,
                serviceName,
                teamName,
                frequencyLabel,
                weekdayLabel,
                `${formattedStartTime}-${formattedEndTime}`,
              ].join(", "),
              value: id,
            };
          },
        ) || [],
    [freeSubscriptions.data, subscriptions],
  );

  const periodOptions = useConst([
    { label: t("per booking"), value: "per booking" },
    { label: t("monthly"), value: "monthly" },
  ]);

  const handleSave = formSubmitHandler(({ ...values }) => {
    setIsSubmitting(true);

    const payload = {
      ...values,
      isPerOrder: values.period === "per booking",
      subscriptionIds: subscriptions.map((subscription) => subscription.id),
    };

    router.patch(`/companies/fixedprices/${fixedPrice.id}`, payload, {
      onFinish: () => setIsSubmitting(false),
      onSuccess: (page) => {
        const {
          flash: { error },
        } = (page as Page<PageProps>).props;

        if (error) {
          return;
        }

        onRefetch();
      },
    });
  });

  return (
    <TabPanel display="flex" flexDirection="column" gap={4} {...props}>
      <AuthorizationGuard permissions="company fixed prices update">
        <Alert
          status="info"
          title={t("info")}
          message={
            t("default fixed price info") +
            "\n" +
            t("fixed price per order info") +
            "\n" +
            t("fixed price laundry info")
          }
          fontSize="small"
          mb={6}
        />
        <Input labelText={t("type")} value={t(fixedPrice.type)} isReadOnly />
        <Autocomplete
          options={periodOptions}
          labelText={t("period")}
          value={period}
          {...register("period", {
            required: t("validation field required"),
          })}
          isRequired
        />
        <Flex gap={4}>
          <Input
            type="date"
            labelText={t("date start")}
            errorText={errors.startDate?.message || serverErrors.startDate}
            {...register("startDate")}
          />
          <Input
            type="date"
            labelText={t("date end")}
            errorText={errors.endDate?.message || serverErrors.endDate}
            {...register("endDate")}
          />
        </Flex>
        <Autocomplete
          options={subscriptionOptions}
          labelText={t("subscriptions")}
          onChange={(e) => {
            const subscription = freeSubscriptions.data?.find(
              (subscription) => subscription.id === Number(e.target.value),
            );

            if (subscription) {
              setSubscriptions((prevState) => [...prevState, subscription]);
            }
          }}
          clearOnSelect
        />
      </AuthorizationGuard>
      <DataTable
        data={subscriptions}
        columns={columns}
        size="md"
        searchable={false}
        paginatable={false}
        actions={[
          (row) => {
            if (
              fixedPrice.type === "laundry" ||
              (["cleaning", "cleaning and laundry"].includes(fixedPrice.type) &&
                subscriptions.length > 1)
            ) {
              return {
                label: t("delete"),
                icon: LuTrash,
                colorScheme: "red",
                color: "red.500",
                _dark: { color: "red.200" },
                onClick: () => {
                  setSubscriptions((prevState) =>
                    prevState.filter(
                      (subscription) => subscription.id !== row.original.id,
                    ),
                  );
                },
              };
            }

            return null;
          },
        ]}
      />
      <AuthorizationGuard permissions="company fixed prices update">
        <Button
          mt={8}
          alignSelf="flex-end"
          fontSize="sm"
          isLoading={isSubmitting}
          loadingText={t("please wait")}
          onClick={handleSave}
        >
          {t("save")}
        </Button>
      </AuthorizationGuard>
    </TabPanel>
  );
};

export default SubscriptionPanel;
