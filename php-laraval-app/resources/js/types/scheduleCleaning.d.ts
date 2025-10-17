import { LaundryOrder } from "./laundryOrder";
import Schedule from "./schedule";
import Subscription from "./subscription";

export default interface ScheduleCleaning {
  id: number;
  subscriptionId: number;
  laundryOrderId: number;
  laundryType: "pickup" | "delivery";
  laundryOrder?: LaundryOrder;
  subscription?: Subscription;
  schedule?: Omit<Schedule, "detail">;
}
