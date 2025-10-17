import { LaundryOrder } from "./laundryOrder";

export interface LaundryOrderSchedule {
  id: number;
  laundryOrderId: number;
  type: string;
  date: string;
  time: string;
  laundryOrder?: LaundryOrder;
}
