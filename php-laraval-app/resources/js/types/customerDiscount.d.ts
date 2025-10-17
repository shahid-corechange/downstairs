import User from "./user";

export type DiscountType = {
  cleaning: "Cleaning";
  laundry: "Laundry";
};

export default interface CustomerDiscount {
  id: number;
  userId: number;
  type: string;
  value: number;
  isActive: boolean;
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
  startDate?: string;
  endDate?: string;
  usageLimit?: number;
  user?: User;
}
