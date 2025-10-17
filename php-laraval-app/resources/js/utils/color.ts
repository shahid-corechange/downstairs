import { TinyColor } from "@ctrl/tinycolor";

import theme from "@/theme";

export const getColor = (
  color: string,
  fallback?: string,
): string | undefined => {
  const keys = color.split(".");
  let value = theme.colors;

  for (let i = 0; i < keys.length; i++) {
    value = value[keys[i]];

    if (!value) {
      return fallback;
    }
  }

  return value;
};

export const transparentize = (color: string, opacity: number) => {
  const raw = getColor(color);
  const newColor = new TinyColor(raw || color);
  newColor.setAlpha(opacity);

  return newColor.toRgbString();
};
