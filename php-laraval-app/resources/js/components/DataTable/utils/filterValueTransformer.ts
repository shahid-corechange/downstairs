import { TIME_FORMAT } from "@/constants/datetime";

import { toDayjs } from "@/utils/datetime";

const defaultListFilterTransformer = (value: unknown) => {
  if (Array.isArray(value)) {
    return value;
  }

  if (typeof value === "string") {
    try {
      return JSON.parse(value);
    } catch {
      return value;
    }
  }

  return value;
};

const defaultTimeTransformer = (value: unknown) => {
  if (typeof value !== "string") {
    return value;
  }

  const [hour, minute] = value.split(":").map(Number);
  return toDayjs()
    .set("hour", hour)
    .set("minute", minute)
    .startOf("minute")
    .utc()
    .format(TIME_FORMAT);
};

const defaultPhoneTransformer = (value: unknown) => {
  if (typeof value !== "string") {
    return value;
  }

  // Remove '+' character and space
  return value.replace(/[\s+]/g, "");
};

export const getDefaultFilterTransformer = (
  value: unknown,
  display?: string,
) => {
  if (display === "list") {
    return defaultListFilterTransformer(value);
  }

  if (display === "time") {
    return defaultTimeTransformer(value);
  }

  if (display === "phone") {
    return defaultPhoneTransformer(value);
  }

  return value;
};
