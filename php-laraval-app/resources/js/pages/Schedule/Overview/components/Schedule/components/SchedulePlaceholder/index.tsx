import { Flex, Icon, Portal } from "@chakra-ui/react";
import { TinyColor } from "@ctrl/tinycolor";
import { useEffect, useMemo, useState } from "react";
import { HiOutlineLockClosed } from "react-icons/hi2";

import { SIMPLE_TIME_FORMAT } from "@/constants/datetime";

import useScheduleStore from "@/pages/Schedule/Overview/store";
import { ScheduleCellInfo } from "@/pages/Schedule/Overview/types";

import { getQuarter, toDayjs } from "@/utils/datetime";
import { calculateCalendarQuarters, isAvailable } from "@/utils/schedule";

const SchedulePlaceholder = () => {
  const schedules = useScheduleStore((state) => state.schedules);
  const schedule = useScheduleStore((state) => state.draggedSchedule);
  const rect = useScheduleStore((state) => state.draggedScheduleRect);
  const setDraggedSchedule = useScheduleStore(
    (state) => state.setDraggedSchedule,
  );
  const setDraggedScheduleRect = useScheduleStore(
    (state) => state.setDraggedScheduleRect,
  );
  const setDragTarget = useScheduleStore((state) => state.setDragTarget);
  const selectedDate = useScheduleStore((state) => state.selectedDate);
  const creationLimitDays = useScheduleStore(
    (state) => state.creationLimitDays,
  );

  const [position, setPosition] = useState({
    x: rect?.x || 0,
    y: rect?.y || 0,
  });
  const [target, setTarget] = useState<ScheduleCellInfo>();
  const [targetColor, setTargetColor] = useState("");
  const [targetHeight, setTargetHeight] = useState<number>();
  const [targetWidth, setTargetWidth] = useState<number>();
  const [isReleased, setIsReleased] = useState(false);

  const iconColor = useMemo(() => {
    if (!schedule || !schedule.isFixed) {
      return "white";
    }

    const color = new TinyColor(schedule.team?.color ?? "");
    return color.isDark() ? "white" : "black";
  }, [schedule]);

  useEffect(() => {
    if (schedule && rect) {
      const { left, top, x, y } = rect;
      const startAt = toDayjs(schedule.startAt);
      const currentTime = toDayjs();

      let quarters = getQuarter(schedule.startAt, schedule.endAt);
      const cellOriginalHeight = rect.height / quarters;

      window.onmousemove = (e) => {
        const element = document.elementFromPoint(e.clientX, e.clientY);
        const time = element?.getAttribute("data-time");
        const team = element?.getAttribute("data-team");
        const teamColor = element?.getAttribute("data-team-color");
        const teamWorkers = element?.getAttribute("data-team-workers");
        const dayIndex = element?.getAttribute("data-day-index");
        const isSameTeam = schedule?.team?.id === Number(team);

        if (element && time && team && teamColor && teamWorkers && dayIndex) {
          const elementRect = element.getBoundingClientRect();
          const newDate = selectedDate.weekday(Number(dayIndex));
          const newStartAt = toDayjs(newDate.format(`YYYY-MM-DDT${time}:00Z`));
          const calendarQuarters = isSameTeam
            ? quarters
            : calculateCalendarQuarters(schedule.quarters, Number(teamWorkers));
          quarters = isSameTeam ? quarters : calendarQuarters;

          if (
            newStartAt.isAfter(currentTime) &&
            isAvailable(
              schedules,
              newStartAt,
              creationLimitDays,
              quarters,
              Number(team),
              schedule.id,
            )
          ) {
            setPosition({
              x: elementRect.left,
              y: elementRect.top,
            });

            setTarget({
              time,
              duration: quarters,
              dayIndex: newDate.weekday(),
              teamId: Number(team),
            });

            setTargetColor(teamColor);
            setTargetHeight(cellOriginalHeight * quarters);
            setTargetWidth(elementRect.width);

            return;
          }
        }

        setPosition({
          x: e.clientX - left,
          y: e.clientY - top,
        });
        setTarget(undefined);
        setTargetColor("");
        setTargetHeight(undefined);
        setTargetWidth(undefined);
      };

      window.onmouseup = () => {
        window.onmousemove = null;
        window.onmouseup = null;

        setIsReleased(true);

        if (
          schedule &&
          target &&
          (startAt.weekday() !== target.dayIndex ||
            startAt.format(SIMPLE_TIME_FORMAT) !== target.time ||
            schedule?.team?.id !== target.teamId)
        ) {
          setDragTarget(target);
        } else {
          setPosition({ x, y });

          window.setTimeout(() => {
            setDraggedSchedule(undefined);
            setDraggedScheduleRect(undefined);
          }, 200);
        }
      };
    }

    return () => {
      window.onmousemove = null;
      window.onmouseup = null;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [rect, schedule, target]);

  return (
    <Portal>
      <Flex
        position="fixed"
        top={0}
        left={0}
        align="center"
        justify="center"
        h={targetHeight ? `${targetHeight}px` : `${rect?.height || 0}px`}
        w={targetWidth ? `${targetWidth}px` : `${rect?.width || 0}px`}
        bg={targetColor || schedule?.team?.color}
        borderRadius="md"
        border="1px"
        borderColor="rgba(0, 0, 0, 0.2)"
        transform={`translate(${position.x}px, ${position.y}px)`}
        transition={isReleased ? "all 0.2s ease-in-out" : undefined}
        opacity={isReleased ? 1 : 0.5}
        cursor="grabbing"
        pointerEvents="none"
        zIndex={1}
      >
        {schedule?.isFixed && (
          <Icon as={HiOutlineLockClosed} color={iconColor} />
        )}
      </Flex>
    </Portal>
  );
};

export default SchedulePlaceholder;
