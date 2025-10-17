import { Dict } from "@/types";

type AnyObject = { [key: string]: AnyObject | string | number | Date };

export const sortedObjectEntries = <T extends Dict>(object: T) => {
  return Object.entries(object).sort(([a], [b]) => a.localeCompare(b));
};

export const flattenObject = (
  obj: AnyObject,
  prefix = "",
  separator = ".",
): AnyObject => {
  return Object.keys(obj).reduce((acc, key) => {
    const newKey = prefix ? `${prefix}${separator}${key}` : key;
    const value = obj[key];

    if (typeof value === "object" && value !== null && !Array.isArray(value)) {
      Object.assign(acc, flattenObject(value as AnyObject, newKey, separator));
    } else {
      acc[newKey] = value;
    }

    return acc;
  }, {} as AnyObject);
};
