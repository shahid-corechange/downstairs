import { LaundryOrder } from "./laundryOrder";

export default interface ScheduleLaundry {
  id: number;
  laundryOrderId: number;
  type: string;
  laundryOrder?: LaundryOrder;
  schedule?: Omit<Schedule, "detail">;
}
