import { round } from "./number";

export const getCurrencyFormatter = (
  language: string,
  currency: string,
  decimalPlaces: number = 2,
) => {
  return new Intl.NumberFormat(language.replace("_", "-"), {
    style: "currency",
    currency,
    minimumFractionDigits: decimalPlaces,
    maximumFractionDigits: decimalPlaces,
  });
};

export const formatCurrency = (
  language: string,
  currency: string,
  value: number,
  decimalPlaces: number = 2,
) => {
  const formatter = getCurrencyFormatter(language, currency, decimalPlaces);
  return formatter.format(value);
};

export const parseCurrency = (
  language: string,
  currency: string,
  value: string,
  decimalPlaces: number = 2,
) => {
  const placeholder = 1234567.89;
  const formatter = getCurrencyFormatter(language, currency, decimalPlaces);
  const parts = formatter.formatToParts(placeholder);

  const groupSeparator =
    parts.find((part) => part.type === "group")?.value ?? ".";
  const decimalSeparator =
    parts.find((part) => part.type === "decimal")?.value ?? ",";

  const cleanValue = value
    .replaceAll(groupSeparator, "")
    .replaceAll(decimalSeparator, ".")
    .replace(/[^0-9.-]/g, "");

  const lastDecimalIndex = cleanValue.lastIndexOf(".");
  if (lastDecimalIndex === -1) {
    return round(Number(cleanValue), decimalPlaces);
  }

  // Remove decimal separator if more than one and keep only the last one
  const thousandPart = cleanValue.slice(0, lastDecimalIndex).replace(/\./g, "");
  const decimalPart = cleanValue.slice(
    lastDecimalIndex,
    lastDecimalIndex + decimalPlaces + 1,
  ); // +1 to include the decimal point

  return round(Number(`${thousandPart}${decimalPart}`), decimalPlaces);
};
