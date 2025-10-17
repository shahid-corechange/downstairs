import { Button, Flex, Textarea } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { Dayjs } from "dayjs";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import { useWizard } from "@/components/Wizard/hooks";

import { DATE_FORMAT, SIMPLE_TIME_FORMAT } from "@/constants/datetime";

import { useError } from "@/hooks/error";

import { ScheduleOverviewPageProps } from "@/pages/Schedule/Overview/types";

import { useGetCompanyUsers } from "@/services/company";
import {
  useGetCustomerAddresses,
  useGetCustomerProperties,
  useGetCustomers,
} from "@/services/customer";
import { useGetServices } from "@/services/service";

import Team from "@/types/team";

import { toDayjs } from "@/utils/datetime";

import { PageProps } from "@/types";

import { PlanFormValues, StepsValues } from "../../types";

interface PlanStepProps {
  customerType: "private" | "company";
  team: Team;
  startAt: Dayjs;
}

const PlanStep = ({ customerType, team, startAt }: PlanStepProps) => {
  const { t } = useTranslation();

  const { transportPrice, materialPrice } =
    usePage<PageProps<ScheduleOverviewPageProps>>().props;

  const { validationError } = useError({ clearOnUnmount: false });

  const {
    stepsValues,
    isValidating,
    hideStep,
    showStep,
    moveTo,
    onValidateSuccess,
    onValidateError,
  } = useWizard<StepsValues, PlanFormValues>();

  const [isFirstRender, setIsFirstRender] = useState(true);

  const {
    register,
    watch,
    setValue,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<PlanFormValues>({
    defaultValues: { ...stepsValues[0], utcStartAt: startAt.utc() },
  });

  const userId = watch("userId");
  const propertyId = watch("propertyId");
  const serviceId = watch("serviceId");
  const addonIds = watch("addonIds");
  const quarters = watch("quarters");

  const customers = useGetCustomers({
    request: {
      size: -1,
      show: "active",
      only: ["id", "fullname", "cellphone"],
    },
    query: {
      enabled: customerType === "private",
      staleTime: Infinity,
    },
  });

  const companies = useGetCompanyUsers({
    request: {
      size: -1,
      show: "active",
      only: ["id", "fullname", "cellphone"],
    },
    query: {
      enabled: customerType === "company",
      staleTime: Infinity,
    },
  });

  const services = useGetServices({
    request: {
      size: -1,
      show: "active",
      include: ["addons", "quarters"],
      filter: {
        eq: {
          membershipType: customerType,
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

  const customerProperties = useGetCustomerProperties(userId, {
    request: {
      filter: {
        eq: {
          membershipType: customerType,
        },
      },
      size: -1,
      only: ["id", "address.address", "squareMeter"],
    },
  });

  const customerAddresses = useGetCustomerAddresses(userId, {
    request: {
      filter: {
        eq: {
          membershipType: customerType,
        },
      },
      size: -1,
      only: ["id", "name", "reference", "type", "address.address"],
    },
  });

  const propertyOptions = useMemo(
    () =>
      customerProperties.data
        ? customerProperties.data.map((property) => ({
            label: property.address?.address ?? "",
            value: property.id,
          }))
        : [],
    [customerProperties.data],
  );

  const addressOptions = useMemo(
    () =>
      customerAddresses.data?.map((customer) => {
        const address = customer.address?.address ?? "";
        const name = customer.name ?? "";
        const reference = customer.reference ?? "";
        const type = customer.type ?? "";

        let label = `${address} - ${name}`;
        label += reference ? ` - ${reference}` : "";
        label += ` (${t(type)})`;

        return {
          label,
          value: customer.id,
        };
      }) ?? [],
    [customerAddresses.data],
  );

  const userOptions = useMemo(
    () =>
      (customerType === "private" ? customers.data : companies.data)?.map(
        (user) => ({
          label: user.fullname + (user.cellphone ? ` - ${user.cellphone}` : ""),
          value: user.id,
        }),
      ) ?? [],

    [customerType, customers.data, companies.data],
  );

  const serviceOptions = useMemo(
    () =>
      services.data?.map((item) => ({
        label: `${item.name} (SEK ${item.priceWithVat})`,
        value: item.id,
      })) ?? [],
    [services.data],
  );

  const addonOptions = useMemo(
    () =>
      services?.data
        ?.find((item) => item.id === serviceId)
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

    [services.data, serviceId],
  );

  const handleSubmit = formSubmitHandler(onValidateSuccess, onValidateError);

  useEffect(() => {
    setIsFirstRender(false);
  }, []);

  useEffect(() => {
    const service = services.data?.find((item) => item.id === serviceId);
    const property = customerProperties.data?.find(
      (item) => item.id === propertyId,
    );

    if (!service || !property) {
      return;
    }

    const serviceQuarters = (service.quarters ?? []).reduce<number>(
      (result, item) => {
        if (
          property.squareMeter >= item.minSquareMeters &&
          property.squareMeter <= item.maxSquareMeters
        ) {
          return item.quarters;
        }

        return result;
      },
      0,
    );

    if (!isFirstRender) {
      setValue("quarters", serviceQuarters);
    }
  }, [services.data, serviceId, propertyId]);

  useEffect(() => {
    if (propertyOptions.length === 1) {
      setValue("propertyId", propertyOptions[0].value);
    }

    if (addressOptions.length === 1) {
      setValue("customerId", addressOptions[0].value);
    }
  }, [propertyOptions, addressOptions]);

  useEffect(() => {
    const service = services.data?.find((item) => item.id === serviceId);

    if (!service) {
      return;
    }

    const parsedAddonIds = addonIds ? JSON.parse(addonIds) : [];

    let totalPrice =
      (service.priceWithVat + materialPrice) * quarters + transportPrice;

    for (const addon of service.addons ?? []) {
      if (!parsedAddonIds.includes(addon.id)) {
        continue;
      }

      totalPrice += addon.priceWithVat;
    }

    totalPrice = isNaN(totalPrice) ? 0 : totalPrice;
    setValue("calculatedPrice", totalPrice);

    if (!isFirstRender) {
      setValue("totalPrice", totalPrice);
    }
  }, [
    services.data,
    serviceId,
    addonIds,
    quarters,
    transportPrice,
    materialPrice,
  ]);

  useEffect(() => {
    const calendarQuarters = Math.ceil(quarters / (team.users?.length ?? 1));
    const endAt = startAt.add(calendarQuarters * 15, "minute");

    setValue("startAt", startAt.format(DATE_FORMAT));
    setValue("startTimeAt", startAt.format(SIMPLE_TIME_FORMAT));
    setValue("utcEndAt", endAt.utc());
    setValue("endAt", endAt.format(DATE_FORMAT));
    setValue("endTimeAt", endAt.format(SIMPLE_TIME_FORMAT));

    if (endAt.isBefore(toDayjs())) {
      // if it's a historical schedule, show the worker step
      showStep(1);
    } else {
      // if it's a future schedule, hide the worker step
      hideStep(1);
    }
  }, [quarters, team, startAt]);

  useEffect(() => {
    if (isValidating) {
      handleSubmit();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isValidating]);

  return (
    <Flex
      as="form"
      w="full"
      direction="column"
      gap={4}
      onSubmit={(e) => {
        e.preventDefault();
        moveTo("next");
      }}
      autoComplete="off"
      noValidate
    >
      <Autocomplete
        options={userOptions}
        labelText={t("customer")}
        errorText={errors.userId?.message || validationError.userId}
        value={userId}
        {...register("userId", {
          required: t("validation field required"),
          valueAsNumber: true,
        })}
        isRequired
      />
      <Autocomplete
        options={propertyOptions}
        labelText={t("property")}
        errorText={errors.propertyId?.message || validationError.propertyId}
        value={propertyId}
        {...register("propertyId", {
          required: t("validation field required"),
          valueAsNumber: true,
        })}
        isLoading={customerProperties.isLoading}
        isRequired
      />
      <Autocomplete
        options={addressOptions}
        labelText={t("invoice address")}
        errorText={errors.customerId?.message || validationError.customerId}
        value={watch("customerId")}
        {...register("customerId", {
          required: t("validation field required"),
          valueAsNumber: true,
        })}
        isRequired
        isLoading={customerAddresses.isLoading}
      />
      <Flex gap={4}>
        <Input labelText={t("team")} defaultValue={team.name} isReadOnly />
        <Autocomplete
          options={serviceOptions}
          labelText={t("service")}
          errorText={errors.serviceId?.message || validationError.teamId}
          value={watch("serviceId")}
          {...register("serviceId", {
            required: t("validation field required"),
            valueAsNumber: true,
            onChange: () => {
              setValue("addonIds", "");
            },
          })}
          isRequired
        />
      </Flex>
      <Autocomplete
        options={addonOptions}
        labelText={t("add ons")}
        errorText={errors.addonIds?.message || validationError.workerIds}
        value={watch("addonIds")}
        {...register("addonIds")}
        multiple
      />
      <Input
        as={Textarea}
        labelText={t("note")}
        errorText={errors.description?.message || validationError.description}
        resize="none"
        {...register("description")}
      />
      <Flex gap={4}>
        <Input
          type="number"
          labelText={t("total quarters")}
          min={1}
          errorText={errors.quarters?.message || validationError.quarters}
          {...register("quarters", {
            required: t("validation field required"),
            min: { value: 1, message: t("validation field min", { min: 1 }) },
            valueAsNumber: true,
          })}
          isRequired
        />
        <Input
          type="number"
          labelText={t("total price")}
          helperText={t("include transport and material")}
          min={1}
          errorText={errors.totalPrice?.message || validationError.totalPrice}
          {...register("totalPrice", {
            required: t("validation field required"),
            min: { value: 1, message: t("validation field min", { min: 1 }) },
            valueAsNumber: true,
          })}
          isRequired
        />
      </Flex>
      <Button type="submit" opacity={0} visibility="hidden" />
    </Flex>
  );
};

export default PlanStep;
