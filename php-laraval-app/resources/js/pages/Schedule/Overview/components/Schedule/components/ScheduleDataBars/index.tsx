import { Box, Flex } from "@chakra-ui/react";
import { useEffect, useState } from "react";

import { SIMPLE_TIME_FORMAT } from "@/constants/datetime";

import useScheduleStore from "@/pages/Schedule/Overview/store";

import Schedule from "@/types/schedule";

import { toDayjs } from "@/utils/datetime";

import ScheduleContent from "../ScheduleContent";

type ScheduleBar = {
  top: number;
  left: number;
  width: number;
  schedules: (Schedule & { isJoint?: boolean })[];
};

const ScheduleDataBars = () => {
  const filteredSchedules = useScheduleStore(
    (state) => state.filteredSchedules,
  );
  const selectedDate = useScheduleStore((state) => state.selectedDate);
  const showWeekend = useScheduleStore((state) => state.showWeekend);
  const [tableRect, setTableRect] = useState<DOMRect | null>(null);
  const [scheduleBars, setScheduleBars] = useState<Record<string, ScheduleBar>>(
    {},
  );

  useEffect(() => {
    const startWeek = selectedDate.weekday(0);
    const endWeek = selectedDate.weekday(showWeekend ? 6 : 4).endOf("day");

    const allBars = filteredSchedules.reduce<Record<string, ScheduleBar>>(
      (acc, schedule) => {
        const startAt = toDayjs(schedule.startAt);
        const endAt = toDayjs(schedule.endAt);
        const teamId = schedule.team?.id ?? "";
        const dayIndex = startAt.weekday();
        const time = startAt.format(SIMPLE_TIME_FORMAT);

        if (startAt.isBefore(startWeek) || endAt.isAfter(endWeek)) {
          return acc;
        }

        const cell = document.querySelector<HTMLElement>(
          `[data-team='${teamId}'][data-day-index='${dayIndex}'][data-time='${time}']`,
        );

        if (!cell) {
          return acc;
        }

        const key = `${teamId}-${dayIndex}-${time}`;

        if (!acc[key]) {
          acc[key] = {
            top: cell.offsetTop,
            left: cell.offsetLeft,
            width: cell.getBoundingClientRect().width,
            schedules: [],
          };
        }

        acc[key].schedules.push(schedule);

        if (startAt.isBefore(endAt, "date") && !endAt.isAfter(endWeek)) {
          const jointDayIndex = endAt.weekday();
          const jointTime = "00:00";
          const jointCell = document.querySelector<HTMLElement>(
            `[data-team='${teamId}'][data-day-index='${jointDayIndex}'][data-time='${jointTime}']`,
          );

          if (!jointCell) {
            return acc;
          }

          const jointKey = `${teamId}-${jointDayIndex}-${jointTime}`;

          if (!acc[jointKey]) {
            acc[jointKey] = {
              top: jointCell.offsetTop,
              left: jointCell.offsetLeft,
              width: jointCell.getBoundingClientRect().width,
              schedules: [],
            };
          }

          acc[jointKey].schedules.push({ ...schedule, isJoint: true });
        }

        return acc;
      },
      {},
    );

    setScheduleBars(allBars);
  }, [tableRect, selectedDate, filteredSchedules, showWeekend]);

  useEffect(() => {
    const observer = new ResizeObserver((entries) => {
      for (const entry of entries) {
        const tableRect = entry.target.getBoundingClientRect();
        setTableRect(tableRect);
      }
    });

    const interval = window.setInterval(() => {
      const table = document.querySelector<HTMLElement>("#schedule-table");

      if (table) {
        observer.observe(table);
        window.clearInterval(interval);
      }
    }, 100);

    return () => {
      observer.disconnect();
    };
  }, [filteredSchedules]);

  return (
    <Box pos="absolute" h="full" w="full" overflowY="clip" pointerEvents="none">
      {Object.entries(scheduleBars).map(
        ([key, scheduleBar]) =>
          scheduleBar && (
            <Flex
              key={key}
              pos="absolute"
              top={`${scheduleBar.top}px`}
              left={`${scheduleBar.left}px`}
              w={`${scheduleBar.width}px`}
              gap="1px"
              zIndex={1}
              pointerEvents="all"
            >
              {scheduleBar.schedules.map((schedule) => (
                <ScheduleContent key={schedule.id} schedule={schedule} />
              ))}
            </Flex>
          ),
      )}
    </Box>
  );
};

export default ScheduleDataBars;
