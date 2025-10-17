import dayjs, { Dayjs } from "dayjs";

import Schedule from "@/types/schedule";

import { toDayjs } from "@/utils/datetime";

export const isAvailable = (
  schedules: Schedule[],
  startAt: dayjs.Dayjs,
  creationLimitDays: number,
  quarters?: number,
  teamId?: number,
  excludeScheduleId?: number,
) => {
  const endAt = quarters
    ? startAt.add(quarters * 15, "minute")
    : startAt.add(1, "day").startOf("day");

  const creationLimit = toDayjs().add(creationLimitDays, "day").endOf("day");

  if (startAt.isAfter(creationLimit)) {
    return false;
  }

  return schedules.every((schedule) => {
    if (
      schedule.team?.id !== teamId ||
      schedule.id === excludeScheduleId ||
      schedule.status === "cancel"
    ) {
      return true;
    }

    const scheduleStartAt = toDayjs(schedule.startAt);
    const scheduleEndAt = toDayjs(schedule.endAt);

    return (
      endAt.isBefore(scheduleStartAt) ||
      endAt.isSame(scheduleStartAt) ||
      startAt.isAfter(scheduleEndAt) ||
      startAt.isSame(scheduleEndAt)
    );
  });
};

export const calculateCalendarQuarters = (
  totalQuarters: number,
  totalWorkers: number = 1,
) => {
  return Math.ceil(totalQuarters / (totalWorkers || 1));
};

export const calculateEndTime = (
  startTime: string | Dayjs,
  calendarQuarters: number,
) => {
  const start = typeof startTime === "string" ? toDayjs(startTime) : startTime;

  return start.add(calendarQuarters * 15, "minute");
};

export const isReadonly = (schedule: Schedule) =>
  ["done", "cancel", "invoiced"].includes(schedule.status);
