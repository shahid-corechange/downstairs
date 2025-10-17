import { LAUNDRY_ORDER_STATUS } from "@/constants/laundryOrder";

export const compareLaundryOrderStatus = (
  position: "before" | "current" | "after",
  targetStatus: (typeof LAUNDRY_ORDER_STATUS)[number],
  currentStatus?: string,
) => {
  if (!currentStatus) {
    return false;
  }

  const currentIndex = LAUNDRY_ORDER_STATUS.indexOf(
    currentStatus as (typeof LAUNDRY_ORDER_STATUS)[number],
  );
  const targetIndex = LAUNDRY_ORDER_STATUS.indexOf(targetStatus);

  if (currentIndex === -1 || targetIndex === -1) {
    return false;
  }

  switch (position) {
    case "before":
      return currentIndex < targetIndex;
    case "current":
      return currentIndex === targetIndex;
    case "after":
      return currentIndex > targetIndex;
    default:
      return false;
  }
};

export const getNextStatus = (status: string): string => {
  const index = LAUNDRY_ORDER_STATUS.indexOf(status);
  return LAUNDRY_ORDER_STATUS[index + 1] || status;
};
