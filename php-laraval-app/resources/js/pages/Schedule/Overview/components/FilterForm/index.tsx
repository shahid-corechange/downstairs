import { Button, Flex, Heading, useConst } from "@chakra-ui/react";
import { useMemo } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";

import { QUARTERS_IN_DAYS, SIMPLE_TIME_FORMAT } from "@/constants/datetime";
import { SCHEDULE_THEAD_HEIGHT } from "@/constants/layout";

import { getTranslatedOptions } from "@/utils/autocomplete";
import { toDayjs } from "@/utils/datetime";

import useScheduleStore from "../../store";
import { ScheduleFilterStatus } from "../../types";

interface FormValues {
  status: ScheduleFilterStatus;
  startTime?: string;
  minQuarter?: number;
  city?: string;
  customer?: string;
}

const FilterForm = () => {
  const { t } = useTranslation();
  const schedules = useScheduleStore((state) => state.schedules);
  const statusFilter = useScheduleStore((state) => state.statusFilter);
  const setCustomerFilter = useScheduleStore(
    (state) => state.setCustomerFilter,
  );
  const setCityFilter = useScheduleStore((state) => state.setCityFilter);
  const setStatusFilter = useScheduleStore((state) => state.setStatusFilter);
  const setStartTimeFilter = useScheduleStore(
    (state) => state.setStartTimeFilter,
  );
  const setMinQuarterFilter = useScheduleStore(
    (state) => state.setMinQuarterFilter,
  );
  const scheduleComponentRef = useScheduleStore(
    (state) => state.scheduleComponentRef,
  );
  const tableComponentRef = useScheduleStore(
    (state) => state.tableComponentRef,
  );

  const {
    register,
    watch,
    reset,
    handleSubmit: formSubmitHandler,
    formState: { isSubmitting, isSubmitted },
  } = useForm<FormValues>({
    defaultValues: {
      status: statusFilter,
    },
  });

  const customerOptions = useMemo(
    () => [
      ...new Set(schedules.map((schedule) => schedule?.user?.fullname ?? "")),
    ],
    [schedules],
  );

  const cityOptions = useMemo(
    () => [
      ...new Set(
        schedules.map(
          (schedule) => schedule?.property?.address?.city?.name ?? "",
        ),
      ),
    ],
    [schedules],
  );

  const statusOptions = useConst(
    getTranslatedOptions([
      "booked",
      "progress",
      "done",
      "cancel",
      "active",
      "all",
    ]),
  );

  const scrollToEarliestSchedule = () => {
    const earliestSchedule = schedules.sort((a, b) => {
      const startAtTime = toDayjs(a.startAt).format(SIMPLE_TIME_FORMAT);
      const endAtTime = toDayjs(b.startAt).format(SIMPLE_TIME_FORMAT);
      return startAtTime > endAtTime ? 1 : -1;
    })[0];

    const extractedFirstHour = toDayjs(earliestSchedule.startAt)
      .subtract(1, "hour")
      .format(SIMPLE_TIME_FORMAT);

    const barOffsetTop = tableComponentRef?.current?.querySelector<HTMLElement>(
      `td[data-time='${extractedFirstHour}']`,
    )?.offsetTop;

    scheduleComponentRef?.current?.scrollTo({
      top: barOffsetTop ? barOffsetTop - SCHEDULE_THEAD_HEIGHT : 0,
      behavior: "smooth",
    });
  };

  const handleSubmit = formSubmitHandler(
    ({ city, customer, startTime, status, minQuarter }) => {
      setCustomerFilter(customer);
      setCityFilter(city);
      setStartTimeFilter(startTime);
      setMinQuarterFilter(minQuarter);
      setStatusFilter(status);

      scrollToEarliestSchedule();
    },
  );

  const handleReset = () => {
    reset({ customer: "", city: "", startTime: "", status: "active" });

    setCustomerFilter("");
    setCityFilter("");
    setStartTimeFilter("");
    setMinQuarterFilter(undefined);
    setStatusFilter("active");

    scrollToEarliestSchedule();
  };

  return (
    <Flex as="form" direction="column" gap={4} onSubmit={handleSubmit}>
      <Heading size="xs" mb={2}>
        {t("filters")}
      </Heading>
      <Autocomplete
        options={customerOptions}
        labelText={t("customer")}
        value={watch("customer")}
        {...register("customer")}
        freeMode
      />
      <Autocomplete
        options={cityOptions}
        labelText={t("postal locality")}
        value={watch("city")}
        {...register("city")}
        freeMode
      />
      <Flex align="center" gap={2}>
        <Autocomplete
          options={QUARTERS_IN_DAYS}
          labelText={t("start time")}
          value={watch("startTime")}
          {...register("startTime")}
          allowEmpty
        />
        <Input
          type="number"
          labelText={t("min duration")}
          placeholder={t("quarter")}
          min={1}
          {...register("minQuarter", { valueAsNumber: true })}
        />
      </Flex>
      <Autocomplete
        options={statusOptions}
        labelText={t("status")}
        value={watch("status")}
        {...register("status")}
      />
      <Flex gap={4}>
        {isSubmitted && Object.values(watch()).some((value) => !!value) && (
          <Button size="sm" colorScheme="gray" flex={1} onClick={handleReset}>
            {t("reset filter")}
          </Button>
        )}
        <Button
          type="submit"
          size="sm"
          flex={1}
          loadingText={t("finding")}
          isLoading={isSubmitting}
        >
          {t("find")}
        </Button>
      </Flex>
    </Flex>
  );
};

export default FilterForm;
