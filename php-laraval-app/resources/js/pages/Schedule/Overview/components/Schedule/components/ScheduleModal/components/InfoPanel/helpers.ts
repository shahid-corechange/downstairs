import { ScheduleItem } from "@/types/schedule";

export const checkProductEqual = (
  products1: ScheduleItem[],
  products2: ScheduleItem[],
) => {
  if (!products1 || !products2) {
    return products1 === products2;
  }

  if (products1.length !== products2.length) {
    return false;
  }

  return products1.every((p1) => {
    return products2.some(
      (p) =>
        p.itemableId === p1.itemableId && p.paymentMethod === p1.paymentMethod,
    );
  });
};
