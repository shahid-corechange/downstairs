import i18n from "@/utils/localization";

import { toDayjs } from "./datetime";
import { humanizeFileSize } from "./file";

export const validateFileSize = (file: File, size: number = 5242880) => {
  if (file.size <= size) {
    return true;
  }

  return i18n.t("validation file size less", { size: humanizeFileSize(size) });
};

export const validateFilesSize = (files?: FileList, size: number = 5242880) => {
  if (!files) {
    return true;
  }

  for (let i = 0; i < files.length; i++) {
    if (validateFileSize(files[i], size) !== true) {
      return i18n.t("validation file size less", {
        size: humanizeFileSize(size),
      });
    }
  }

  return true;
};

export const validateFileExtension = (
  file: File,
  extensions: string[] = [],
) => {
  if (extensions.includes(file.type)) {
    return true;
  }

  return i18n.t("validation file type", { type: extensions.join(", ") });
};

export const validateFilesExtension = (
  files?: FileList,
  extensions: string[] = [],
) => {
  if (!files) {
    return true;
  }

  for (let i = 0; i < files.length; i++) {
    if (validateFileExtension(files[i], extensions) !== true) {
      return i18n.t("validation file type", { type: extensions.join(", ") });
    }
  }

  return true;
};

export const validateEmail = (email: string) => {
  const regex =
    /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;

  return regex.test(email) || i18n.t("validation invalid email");
};

export const isBoolean = (value: unknown): value is boolean =>
  typeof value === "boolean";

export const validatePhone = (phone: string) => {
  const regex = /^\+\d{1,4} \d{4,15}$/;

  return regex.test(phone) || i18n.t("validation invalid phone");
};

export const isValidStartTime = (startTime: string, endTime: string) => {
  if (!startTime || !endTime) {
    return true;
  }

  return startTime < endTime || i18n.t("validation invalid start time");
};

export const isValidEndTime = (startTime: string, endTime: string) => {
  if (!startTime || !endTime) {
    return true;
  }

  return endTime > startTime || i18n.t("validation invalid end time");
};

export const isValidQuarterTime = (datetime: string) => {
  const dayjs = toDayjs(datetime, false);

  return dayjs.minute() % 15 === 0 || i18n.t("validation invalid quarter time");
};

export const isValidMinLength = (value: string, min: number) => {
  if (!value) {
    return true;
  }

  return value.length >= min || i18n.t("validation field min", { min });
};
