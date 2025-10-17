import {
  Box,
  Flex,
  Icon,
  Text,
  Tooltip,
  keyframes,
  useConst,
} from "@chakra-ui/react";
import { TinyColor } from "@ctrl/tinycolor";
import { usePage } from "@inertiajs/react";
import { t } from "i18next";
import { useMemo } from "react";
import { AiOutlineWarning } from "react-icons/ai";
import { HiOutlineLockClosed } from "react-icons/hi2";

import { SCHEDULE_CELL_HEIGHT } from "@/constants/layout";

import { WORK_STATUS_COLORS } from "@/pages/Schedule/Overview/constants";
import useScheduleStore from "@/pages/Schedule/Overview/store";

import Schedule from "@/types/schedule";

import { formatTime, getQuarter, toDayjs } from "@/utils/datetime";
import { isReadonly } from "@/utils/schedule";

import { PageProps } from "@/types";

const animationKeyFrames = keyframes`
  0% {
    background-position: left top, right bottom, left bottom, right top;
    filter: brightness(1);
  }
  50% {
    filter: brightness(0.75);
  }
  100% {
    background-position: left 15px top, right 15px bottom, left bottom 15px, right top 15px;
    filter: brightness(1);
  }
`;

const animation = `${animationKeyFrames} 1s infinite linear`;
const animationStyles = {
  backgroundImage: `linear-gradient(90deg, #eaeaea 50%, transparent 50%), linear-gradient(90deg, #eaeaea 50%, transparent 50%), linear-gradient(0deg, #eaeaea 50%, transparent 50%), linear-gradient(0deg, #eaeaea 50%, transparent 50%)`,
  backgroundRepeat: "repeat-x, repeat-x, repeat-y, repeat-y",
  backgroundSize: "15px 2px, 15px 2px, 2px 15px, 2px 15px",
  backgroundPosition: "left top, right bottom, left bottom, right top",
  animation,
};

interface ScheduleContentProps {
  schedule: Schedule & { isJoint?: boolean };
}

const ScheduleContent = ({ schedule }: ScheduleContentProps) => {
  const { query } = usePage<PageProps>().props;

  const draggedSchedule = useScheduleStore((state) => state.draggedSchedule);
  const selectedDayIndex = useScheduleStore((state) => state.selectedDayIndex);
  const shownTeamIds = useScheduleStore((state) => state.shownTeamIds);
  const setDraggedScheduleRect = useScheduleStore(
    (state) => state.setDraggedScheduleRect,
  );
  const setDraggedSchedule = useScheduleStore(
    (state) => state.setDraggedSchedule,
  );
  const setOpenedScheduleId = useScheduleStore(
    (state) => state.setOpenedScheduleId,
  );

  const isTargeted = useConst(query?.scheduleId === `${schedule.id}`);
  const actualQuarter = useMemo(
    () => getQuarter(schedule.startAt, schedule.endAt),
    [schedule],
  );
  const adaptiveColor = useMemo(() => {
    if (schedule.workStatus && WORK_STATUS_COLORS[schedule.workStatus]) {
      return "white";
    }

    const color = new TinyColor(schedule.team?.color ?? "");
    return color.isDark() ? "white" : "black";
  }, [schedule]);

  const totalQuarter = useMemo(() => {
    const start = toDayjs(schedule.startAt);
    const end = toDayjs(schedule.endAt);

    if (schedule.isJoint) {
      const startOfTomorrow = end.startOf("day");

      return getQuarter(startOfTomorrow, end);
    }

    if (start.isBefore(end, "day")) {
      const startOfTomorrow = end.startOf("day");

      return getQuarter(start, startOfTomorrow);
    }

    return getQuarter(start, end);
  }, [schedule]);

  const handleDrag = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();

    if (isReadonly(schedule)) {
      return;
    }

    const rect = e.currentTarget.getBoundingClientRect();

    setDraggedSchedule(schedule);
    setDraggedScheduleRect({
      height: (rect.height / totalQuarter) * actualQuarter,
      width: rect.width,
      left: e.clientX - rect.left,
      top: e.clientY - rect.top,
      x: rect.x,
      y: rect.y,
    });
  };

  if (selectedDayIndex === null && shownTeamIds.length > 1) {
    return (
      <Tooltip
        label={
          <Box textAlign="center">
            <Text fontSize="sm">
              {`${formatTime(schedule.startAt)} - ${formatTime(
                schedule.endAt,
              )}`}
            </Text>
            <Text fontSize="sm">
              {`${schedule?.user?.fullname ?? ""} (${t(
                schedule.customer?.membershipType ?? "",
              )})`}
              ,
            </Text>
            <Text fontSize="sm">{schedule.property?.address?.city?.name}</Text>
          </Box>
        }
      >
        <Flex
          position="relative"
          h={`${SCHEDULE_CELL_HEIGHT * totalQuarter}px`}
          flex={1}
          bg={
            schedule.workStatus && WORK_STATUS_COLORS[schedule.workStatus]
              ? WORK_STATUS_COLORS[schedule.workStatus]
              : schedule.team?.color ?? ""
          }
          cursor={draggedSchedule?.id === schedule.id ? "grabbing" : "pointer"}
          display={draggedSchedule?.id === schedule.id ? "none" : undefined}
          borderRadius="md"
          border="1px"
          borderColor="inherit"
          align="center"
          justify="center"
          onClick={() => setOpenedScheduleId(schedule.id)}
          onDragStart={handleDrag}
          draggable={!schedule.workStatus}
          {...(isTargeted ? animationStyles : {})}
        >
          {((!schedule.workStatus && schedule.isFixed) ||
            (schedule.workStatus && schedule.hasDeviation)) && (
            <Icon
              as={
                schedule.hasDeviation ? AiOutlineWarning : HiOutlineLockClosed
              }
              color={adaptiveColor}
            />
          )}
        </Flex>
      </Tooltip>
    );
  }

  return (
    <Flex
      h={`${SCHEDULE_CELL_HEIGHT * totalQuarter}px`}
      flexDir="column"
      flex={1}
      bg={
        schedule.workStatus && WORK_STATUS_COLORS[schedule.workStatus]
          ? WORK_STATUS_COLORS[schedule.workStatus]
          : schedule.team?.color ?? ""
      }
      cursor={draggedSchedule?.id === schedule.id ? "grabbing" : "pointer"}
      display={draggedSchedule?.id === schedule.id ? "none" : undefined}
      borderRadius="md"
      border="1px"
      borderColor="inherit"
      align="center"
      overflowY="auto"
      onClick={() => setOpenedScheduleId(schedule.id)}
      onDragStart={handleDrag}
      draggable={!schedule.workStatus}
      {...(isTargeted ? animationStyles : {})}
    >
      <Flex
        flex={1}
        direction="column"
        justify="center"
        align="center"
        py={schedule.isFixed ? 2 : 0}
      >
        {(schedule.isFixed ||
          (schedule.workStatus && schedule.hasDeviation)) && (
          <Icon
            as={schedule.hasDeviation ? AiOutlineWarning : HiOutlineLockClosed}
            color={adaptiveColor}
          />
        )}
        <Text fontSize="sm" color={adaptiveColor}>
          {`${formatTime(schedule.startAt)} - ${formatTime(schedule.endAt)}`}
        </Text>
        <Text textAlign="center" fontSize="sm" color={adaptiveColor}>
          {`${schedule?.user?.fullname ?? ""} (${t(
            schedule.customer?.membershipType ?? "",
          )})`}
        </Text>
        <Text fontSize="sm" color={adaptiveColor}>
          {schedule.property?.address?.city?.name ?? ""}
        </Text>
      </Flex>
    </Flex>
  );
};

export default ScheduleContent;
