import { toDayjs } from "@/utils/datetime";

export const getAdjustedStartAt = (startAt?: string, fromLocalTime = true) => {
  const startAtDayjs = toDayjs(startAt, fromLocalTime);

  if (startAtDayjs.hour() >= 16) {
    // If the start time is after 4 PM, the leave will start the next day
    return startAtDayjs.add(1, "day").startOf("day");
  }

  return startAtDayjs;
};

export const getAdjustedEndAt = (endAt?: string, fromLocalTime = true) => {
  const endAtDayjs = toDayjs(endAt, fromLocalTime);

  if (endAtDayjs.hour() < 8) {
    // If the end time is before 8 AM, the leave will end the previous day
    return endAtDayjs.subtract(1, "day").endOf("day");
  }

  return endAtDayjs;
};
