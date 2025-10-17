export const hasCommonItems = (a: unknown[], b: unknown[]) => {
  return new Set([...a, ...b]).size < a.length + b.length;
};
