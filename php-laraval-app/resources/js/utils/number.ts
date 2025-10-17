export const round = (value: number, decimalPlaces: number = 2) => {
  const multiplier = Math.pow(10, decimalPlaces);
  return Math.round(value * multiplier) / multiplier;
};
