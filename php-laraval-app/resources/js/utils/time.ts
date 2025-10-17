import dayjs, { Dayjs } from "dayjs";
import { t } from "i18next";

import { SIMPLE_TIME_FORMAT } from "@/constants/datetime";

import { toDayjs } from "./datetime";

export const subtractOneHour = (time: string) => {
  // Parse the time string into a dayjs object
  const dateTime = dayjs(`1970-01-01 ${time}`);

  // Subtract 1 hour
  const newDateTime = dateTime.subtract(1, "hour");

  // Format the result as "HH:mm"
  const result = newDateTime.format(SIMPLE_TIME_FORMAT);

  return result;
};

export const humanizeDate = (
  date: Date | Dayjs | string,
  format = "dddd, DD MMMM YYYY",
) => {
  const inputDate = toDayjs(date);
  const today = toDayjs();
  const tommorow = today.add(1, "day");
  const yesterday = today.subtract(1, "day");

  if (inputDate.isSame(today, "date")) {
    return t("today");
  }

  if (inputDate.isSame(tommorow, "date")) {
    return t("tomorrow");
  }

  if (inputDate.isSame(yesterday, "date")) {
    return t("yesterday");
  }

  return inputDate.format(format);
};

export const interpretTotalHours = (totalHours: number) => {
  const sign = totalHours < 0 ? "-" : ""; // Check if totalHours is negative
  const absTotalHours = Math.abs(totalHours); // Work with the absolute value
  const hours = Math.floor(absTotalHours); // Get the absolute hours
  const minutes = Math.round((absTotalHours - hours) * 60); // Calculate the minutes

  if (hours === 0) {
    return `${sign}${minutes} ${t("unit.m")}`;
  } else if (minutes === 0) {
    return `${sign}${hours} ${t("unit.h")}`;
  }

  return `${sign}${hours} ${t("unit.h")} ${minutes} ${t("unit.m")}`;
};
