import { Button, TableCellProps, Td, Tooltip } from "@chakra-ui/react";
import * as _ from "lodash-es";
import { memo, useMemo } from "react";

import { SCHEDULE_CELL_HEIGHT } from "@/constants/layout";

import { usePageModal } from "@/hooks/modal";

import useScheduleStore from "@/pages/Schedule/Overview/store";

import Team from "@/types/team";

import { formatTime, getQuarter, toDayjs } from "@/utils/datetime";

import CreateHistoricalScheduleModal from "./components/CreateHistoricalScheduleModal";
import CreateScheduleModal from "./components/CreateScheduleModal";

interface ScheduleCellProps extends TableCellProps {
  team: Team;
  time: string;
  dayIndex: number;
}

const ScheduleCell = memo(
  ({ team, time, dayIndex, ...props }: ScheduleCellProps) => {
    const { modal, openModal, closeModal } = usePageModal<
      unknown,
      "new" | "historical"
    >();

    const selectedDate = useScheduleStore((state) => state.selectedDate);
    const selectedDayIndex = useScheduleStore(
      (state) => state.selectedDayIndex,
    );
    const showWeekend = useScheduleStore((state) => state.showWeekend);
    const availableBlocks = useScheduleStore((state) => state.availableBlocks);
    const creationLimitDays = useScheduleStore(
      (state) => state.creationLimitDays,
    );

    const availability = useMemo(
      () =>
        availableBlocks.find(
          (item) =>
            item.time === time &&
            item.teamId === team.id &&
            item.dayIndex === dayIndex,
        ),
      [availableBlocks, team, dayIndex, time],
    );

    const startAt = useMemo(() => {
      const [hour, minute] = time.split(":").map(Number);
      return selectedDate.weekday(dayIndex).hour(hour).minute(minute);
    }, [selectedDate, dayIndex, time]);

    const endAt = useMemo(() => {
      if (!availability) {
        return null;
      }

      const durationInMinutes = availability.duration * 15;
      return toDayjs(startAt).add(durationInMinutes, "minute");
    }, [startAt, availability]);

    const totalQuarters = useMemo(() => {
      if (!endAt) {
        return 0;
      }

      if (startAt.isBefore(endAt, "day")) {
        const startOfTomorrow = endAt.startOf("day");

        return getQuarter(startAt, startOfTomorrow);
      }

      return getQuarter(startAt, endAt);
    }, [startAt, endAt]);

    if (selectedDayIndex !== null && selectedDayIndex !== dayIndex) {
      return null;
    }

    const isAfterToday = startAt.isAfter(toDayjs());
    const maxCreationDate = toDayjs()
      .add(creationLimitDays, "day")
      .endOf("day");
    const isOutOfRange = startAt.isAfter(maxCreationDate);

    return (
      <>
        <Tooltip label={formatTime(startAt)}>
          <Td
            key={`${team.id}-${time}`}
            h={5}
            p={0}
            position="relative"
            borderRight="1px"
            borderColor="inherit"
            cursor={isOutOfRange ? "default" : "pointer"}
            borderRightColor={dayIndex === 6 ? "gray.400" : "inherit"}
            data-time={time}
            data-team={team.id}
            data-team-color={team.color}
            data-team-workers={team.totalWorkers}
            data-day-index={dayIndex}
            colSpan={selectedDayIndex !== null ? (showWeekend ? 7 : 5) : 1}
            onClick={
              !isOutOfRange
                ? () => openModal(isAfterToday ? "new" : "historical")
                : undefined
            }
            {...props}
          >
            {availability && (
              <Button
                onClick={() => openModal("new")}
                pos="absolute"
                top={0}
                left={0}
                h={`${SCHEDULE_CELL_HEIGHT * totalQuarters}px`}
                minW={0}
                w="full"
                px={0}
                border="1px"
                borderRadius="md"
                borderStyle="solid"
                borderColor="brand.500"
                backgroundColor="white"
                _hover={{ bg: "brand.100" }}
                zIndex={1}
              />
            )}
          </Td>
        </Tooltip>
        <CreateScheduleModal
          isOpen={modal === "new"}
          onClose={closeModal}
          startAt={startAt.toISOString()}
          endAt={endAt?.toISOString() || ""}
          team={team}
        />
        <CreateHistoricalScheduleModal
          isOpen={modal === "historical"}
          onClose={closeModal}
          team={team}
          startAt={startAt}
        />
      </>
    );
  },
  (prev, next) => {
    return _.isEqual(prev, next);
  },
);

export default ScheduleCell;
