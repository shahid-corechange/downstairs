import { LaundryOrder } from "./laundryOrder";
import { User } from "./user";

export interface LaundryOrderHistory {
  id: number;
  laundryOrderId: number;
  type: string;
  note: string;
  laundryOrder?: LaundryOrder;
  causer?: User;
  createdAt: string;
  updatedAt: string;
}
