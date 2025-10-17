export const WEEKDAYS = [
  "Monday",
  "Tuesday",
  "Wednesday",
  "Thursday",
  "Friday",
  "Saturday",
  "Sunday",
] as const;

export const SHORT_WEEKDAYS = [
  "Mon",
  "Tue",
  "Wed",
  "Thu",
  "Fri",
  "Sat",
  "Sun",
] as const;

export const MONTHS = [
  "January",
  "February",
  "March",
  "April",
  "May",
  "June",
  "July",
  "August",
  "September",
  "October",
  "November",
  "December",
] as const;

export const QUARTERS_IN_DAYS = [...new Array(24 * 4)].map((_, i) => {
  const hour = `${Math.floor(i / 4)}`.padStart(2, "0");
  const minute = `${(i % 4) * 15}`.padStart(2, "0");

  return `${hour}:${minute}`;
});

export const HOUR_IN_DAYS = [...new Array(24)].map(
  (_, i) => `${i}`.padStart(2, "0") + ":00",
);

export const DATE_ATOM_FORMAT = "YYYY-MM-DDTHH:mm:ssZ" as const;
export const DATE_FORMAT = "YYYY-MM-DD" as const;
export const DATETIME_FORMAT = "YYYY-MM-DD HH:mm:ss" as const;
export const SIMPLE_DATETIME_FORMAT = "YYYY-MM-DD HH:mm" as const;
export const TIME_FORMAT = "HH:mm:ss" as const;
export const SIMPLE_TIME_FORMAT = "HH:mm" as const;
export const TIME_12_HOUR_FORMAT = "h:mm a" as const;
export const DETAILED_DATETIME_24H_FORMAT = "dddd, MMMM D, YYYY HH:mm" as const;

export const DATE_TIME_UNITS = [
  "year",
  "month",
  "date",
  "hour",
  "minute",
  "second",
] as const;

export const CURRENT_YEAR = new Date().getFullYear();
