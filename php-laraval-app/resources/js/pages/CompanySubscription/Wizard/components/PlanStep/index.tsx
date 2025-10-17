import { Button, Flex, Textarea } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";
import { useWizard } from "@/components/Wizard/hooks";

import { useGetCompanyFixedPrices } from "@/services/companyFixedPrice";
import {
  useGetCustomerAddresses,
  useGetCustomerProperties,
} from "@/services/customer";

import { PageProps } from "@/types";

import {
  CompanySubscriptionWizardPageProps,
  PlanFormValues,
  StepsValues,
} from "../../types";

const PlanStep = () => {
  const { t } = useTranslation();

  const {
    users,
    teams,
    services,
    transportPrice,
    materialPrice,
    query,
    errors: serverErrors,
  } = usePage<PageProps<CompanySubscriptionWizardPageProps>>().props;

  const {
    stepsValues,
    isValidating,
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
    defaultValues: {
      ...stepsValues[0],
      quarters: stepsValues[0].quarters ?? 0,
      teamId: query?.teamId ? Number(query.teamId) : stepsValues[0].teamId,
    },
  });

  const userId = watch("userId");
  const propertyId = watch("propertyId");
  const teamId = watch("teamId");
  const serviceId = watch("serviceId");
  const addonIds = watch("addonIds");
  const quarters = watch("quarters");
  const fixedPriceId = watch("fixedPriceId");

  /**
   * We use the customer endpoint to get the addresses and properties
   * because the server gave us the company user id instead of the company customer id.
   */
  const customerProperties = useGetCustomerProperties(userId, {
    request: {
      filter: {
        eq: {
          membershipType: "company",
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
          membershipType: "company",
        },
      },
      size: -1,
      only: ["id", "name", "reference", "type", "address.address"],
    },
  });

  const fixedPrices = useGetCompanyFixedPrices({
    request: {
      size: -1,
      show: "active",
      include: ["user", "rows"],
      only: ["id", "user.fullname", "rows.type", "rows.priceWithVat"],
      filter: {
        eq: {
          userId,
        },
      },
    },
    query: {
      enabled: !!userId,
    },
  });

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
      customerAddresses.data
        ? customerAddresses.data.map((customer) => {
            let label = `${customer.address?.address} - ${customer.name}`;
            label += customer.reference ? ` - ${customer.reference}` : "";
            label += ` (${t(customer.type)})`;

            return {
              label,
              value: customer.id,
            };
          })
        : [],
    [customerAddresses.data],
  );

  const userOptions = useMemo(
    () =>
      users.map((user) => ({
        label: user.fullname + (user.cellphone ? ` - ${user.cellphone}` : ""),
        value: user.id,
      })),

    // eslint-disable-next-line react-hooks/exhaustive-deps
    [],
  );

  const serviceOptions = useMemo(
    () =>
      services.map((item) => ({
        label: `${item.name} (SEK ${item.priceWithVat})`,
        value: item.id,
      })),
    [services],
  );

  const addonOptions = useMemo(
    () =>
      services
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

    [services, serviceId],
  );

  const fixedPriceOptions = useMemo(() => {
    return fixedPrices.data?.data.map((item) => ({
      value: item.id,
      label: `#${item.id} - ${item.user?.fullname ?? ""}`,
    }));
  }, [fixedPrices.data]);

  const fixedPriceServiceRow = useMemo(() => {
    const selectedFixedPrice = fixedPrices.data?.data.find(
      (item) => item.id === fixedPriceId,
    );

    return selectedFixedPrice?.rows?.find((item) => item.type === "service");
  }, [fixedPriceId, fixedPrices.data?.data]);

  const handleSubmit = formSubmitHandler(onValidateSuccess, onValidateError);

  useEffect(() => {
    setIsFirstRender(false);
  }, []);

  useEffect(() => {
    const service = services.find((item) => item.id === serviceId);
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
  }, [services, serviceId, propertyId]);

  useEffect(() => {
    if (propertyOptions.length === 1) {
      setValue("propertyId", propertyOptions[0].value);
    }

    if (addressOptions.length === 1) {
      setValue("customerId", addressOptions[0].value);
    }
  }, [propertyOptions, addressOptions]);

  useEffect(() => {
    const service = services.find((item) => item.id === serviceId);

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
  }, [services, serviceId, addonIds, quarters, transportPrice, materialPrice]);

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
        errorText={errors.userId?.message || serverErrors.userId}
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
        errorText={errors.propertyId?.message || serverErrors.propertyId}
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
        errorText={errors.customerId?.message || serverErrors.customerId}
        value={watch("customerId")}
        {...register("customerId", {
          required: t("validation field required"),
          valueAsNumber: true,
        })}
        isRequired
        isLoading={customerAddresses.isLoading}
      />
      <Flex gap={4}>
        <Autocomplete
          options={teamOptions}
          labelText={t("team")}
          errorText={errors.teamId?.message || serverErrors.teamId}
          value={teamId}
          {...register("teamId", {
            valueAsNumber: true,
          })}
          allowEmpty
        />
        <Autocomplete
          options={serviceOptions}
          labelText={t("service")}
          errorText={errors.serviceId?.message || serverErrors.serviceId}
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
        errorText={errors.addonIds?.message || serverErrors.addonIds}
        value={watch("addonIds")}
        {...register("addonIds")}
        multiple
      />
      <Autocomplete
        options={fixedPriceOptions ?? []}
        labelText={t("fixed price")}
        errorText={errors.fixedPriceId?.message || serverErrors.fixedPriceId}
        value={fixedPriceId}
        {...register("fixedPriceId", {
          valueAsNumber: true,
        })}
        isLoading={fixedPrices.isLoading}
        allowEmpty
      />
      <Input
        as={Textarea}
        labelText={t("note")}
        helperText={t("subscription note helper text")}
        errorText={errors.description?.message || serverErrors.description}
        resize="none"
        {...register("description")}
      />
      <Flex gap={4}>
        <Input
          type="number"
          labelText={t("total quarters")}
          min={1}
          errorText={errors.quarters?.message || serverErrors.quarters}
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
          errorText={errors.totalPrice?.message || serverErrors.totalPrice}
          value={
            fixedPriceId
              ? fixedPriceServiceRow?.priceWithVat
              : watch("totalPrice")
          }
          {...register("totalPrice", {
            required: t("validation field required"),
            min: { value: 1, message: t("validation field min", { min: 1 }) },
            valueAsNumber: true,
          })}
          isDisabled={!!fixedPriceId}
          isRequired
        />
      </Flex>
      <Button type="submit" opacity={0} visibility="hidden" />
    </Flex>
  );
};

export default PlanStep;
