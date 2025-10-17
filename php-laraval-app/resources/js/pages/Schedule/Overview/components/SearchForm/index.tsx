import { Button, Flex, Heading } from "@chakra-ui/react";
import dayjs from "dayjs";
import { t } from "i18next";
import { useForm } from "react-hook-form";

import Autocomplete from "@/components/Autocomplete";
import Input from "@/components/Input";

import {
  DATE_ATOM_FORMAT,
  QUARTERS_IN_DAYS,
  SIMPLE_TIME_FORMAT,
} from "@/constants/datetime";
import { SCHEDULE_THEAD_HEIGHT } from "@/constants/layout";

import { toDayjs } from "@/utils/datetime";
import { subtractOneHour } from "@/utils/time";

import useScheduleStore from "../../store";
import { ScheduleCellInfo } from "../../types";

interface FormValues {
  startTime?: string;
  duration?: number;
}

const SearchForm = () => {
  const schedules = useScheduleStore((state) => state.schedules);
  const teams = useScheduleStore((state) => state.teams);
  const selectedDate = useScheduleStore((state) => state.selectedDate);
  const setAvailableBlocks = useScheduleStore(
    (state) => state.setAvailableBlocks,
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
    handleSubmit: formSubmitHandler,
    formState: { isSubmitting },
  } = useForm<FormValues>();

  const handleSubmit = formSubmitHandler(({ duration, startTime }) => {
    if (!duration || !startTime) {
      setAvailableBlocks([]);
      return;
    }

    const availableBarOffset =
      tableComponentRef?.current?.querySelector<HTMLElement>(
        `td[data-time='${
          startTime > "01:00" ? subtractOneHour(startTime) : "00:00"
        }']`,
      )?.offsetTop;

    scheduleComponentRef?.current?.scrollTo({
      top: availableBarOffset ? availableBarOffset - SCHEDULE_THEAD_HEIGHT : 0,
      behavior: "smooth",
    });

    const allBlocks = teams.flatMap((team) =>
      [...Array(7).keys()].map<ScheduleCellInfo>((i) => ({
        time: startTime,
        teamId: team.id,
        dayIndex: i,
        duration,
      })),
    );

    const availableBlocks = allBlocks.filter((block) => {
      const [hours, minutes] = block.time.split(":").map(Number);

      const dayOfWeek = selectedDate
        .weekday(block.dayIndex)
        .hour(hours)
        .minute(minutes);

      if (dayOfWeek.isBefore(toDayjs())) {
        return false;
      }

      const startDuration = dayjs.duration({ hours, minutes });
      const endDuration = startDuration.add(duration * 15, "minutes");

      const blockStartTime = startDuration.format(SIMPLE_TIME_FORMAT);
      const blockEndTime = endDuration.format(SIMPLE_TIME_FORMAT);

      return !schedules.some((schedule) => {
        let startAt = toDayjs(schedule.startAt);
        const endAt = toDayjs(schedule.endAt);

        if (startAt.weekday() < block.dayIndex) {
          startAt = endAt.startOf("day");
        }

        const blockStartAt = startAt.format(`YYYY-MM-DDT${blockStartTime}:00Z`);
        const blockEndAt =
          blockEndTime < blockStartTime
            ? endAt.format(`YYYY-MM-DDT${blockEndTime}:00Z`)
            : startAt.format(`YYYY-MM-DDT${blockEndTime}:00Z`);

        return (
          schedule?.team?.id === block.teamId &&
          startAt.weekday() === block.dayIndex &&
          blockStartAt <= endAt.format(DATE_ATOM_FORMAT) &&
          blockEndAt >= startAt.format(DATE_ATOM_FORMAT)
        );
      });
    });

    setAvailableBlocks(availableBlocks);
  });

  return (
    <Flex as="form" direction="column" gap={4} onSubmit={handleSubmit}>
      <Heading size="xs" mb={2}>
        {t("find availability")}
      </Heading>
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
          {...register("duration", { valueAsNumber: true })}
        />
      </Flex>
      <Button
        size="sm"
        type="submit"
        loadingText={t("finding")}
        isLoading={isSubmitting}
      >
        {t("find")}
      </Button>
    </Flex>
  );
};

export default SearchForm;
