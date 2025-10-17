import dayjs, { Dayjs } from "dayjs";

import { DATE_TIME_UNITS, MONTHS } from "@/constants/datetime";

import i18n from "./localization";

export const toDayjs = (
  date?: dayjs.ConfigType,
  fromLocalTime: boolean = true,
) => {
  try {
    return fromLocalTime ? dayjs(date).tz() : dayjs.tz(date);
  } catch {
    return fromLocalTime ? dayjs().tz() : dayjs.tz();
  }
};

export const getWeek = (date: Dayjs) => {
  const dayIndexes = [...Array(7).keys()];
  // .tz().weekday(i).startOf("day") for handling DST
  const week = dayIndexes.map((i) =>
    date.weekday(i).tz().weekday(i).startOf("day"),
  );

  return week;
};

export const getWeeksOfMonth = (date: Dayjs) => {
  const firstDayOfMonth = date.startOf("month");
  const lastDayOfMonth = date.endOf("month");

  const firstDayOfFirstWeek = firstDayOfMonth.weekday(0);
  const lastDayOfLastWeek = lastDayOfMonth.weekday(6);

  const weeks = [];

  let currentDateIteration = firstDayOfFirstWeek;
  while (currentDateIteration.isBefore(lastDayOfLastWeek)) {
    weeks.push(getWeek(currentDateIteration));

    // .tz().add(1, "day").startOf("week") for handling DST
    currentDateIteration = currentDateIteration
      .add(1, "week")
      .tz()
      .add(1, "day")
      .startOf("week");
  }

  return weeks;
};

export const getQuarter = (
  startDate?: dayjs.Dayjs | Date | string | number,
  endDate?: dayjs.Dayjs | Date | string | number,
) => {
  if (!startDate || !endDate) {
    return 0;
  }

  const start = toDayjs(startDate);
  const end = toDayjs(endDate);

  return Math.ceil(end.diff(start, "minute") / 15);
};

export const formatDate = (
  value?: dayjs.Dayjs | Date | string | number,
  format?: string,
  fromLocalTime = true,
) =>
  toDayjs(
    value,
    typeof value === "string" && /^\d{4}-\d{2}-\d{2}$/.test(value)
      ? false
      : fromLocalTime,
  ).format(format ?? "LL");

export const formatTime = (
  value?: dayjs.Dayjs | Date | string | number,
  format?: string,
) => toDayjs(value).format(format ?? "LT");

export const formatDateTime = (
  value?: dayjs.Dayjs | Date | string | number,
  format?: string,
) => toDayjs(value).format(format ?? "LLL");

type ToUTCOptions = {
  year?: number;
  month?: number;
  date?: number;
  hour?: number;
  minute?: number;
  second?: number;
};

export const toUTC = (options: ToUTCOptions, base?: dayjs.ConfigType) => {
  let baseDate = toDayjs(base);

  let lastDefinedUnit = null;

  for (const unit of DATE_TIME_UNITS) {
    const unitValue = options[unit];

    if (unitValue !== undefined) {
      baseDate = baseDate.set(unit, unitValue);
      lastDefinedUnit = unit;
    }
  }

  if (lastDefinedUnit) {
    baseDate = baseDate.startOf(lastDefinedUnit);
  }

  return baseDate.utc();
};

export const getMonthName = (monthNumber: number) => {
  return i18n.t(MONTHS[monthNumber - 1]);
};

export const convertWeeksToDays = (weeks: number): number => {
  return weeks * 7 + Math.floor(weeks / 52);
};

export const addBusinessDays = (date: Dayjs, hours: number) => {
  let adjustedDate = date;

  // Calculate total days needed (including partial days)
  const totalDays = Math.ceil(hours / 24);
  let businessDaysAdded = 0;

  // Add days one by one, counting only business days
  while (businessDaysAdded < totalDays) {
    adjustedDate = adjustedDate.add(1, "day");
    // Only count if it's a business day (Monday-Friday)
    if (adjustedDate.day() !== 0 && adjustedDate.day() !== 6) {
      businessDaysAdded++;
    }
  }

  return adjustedDate;
};
