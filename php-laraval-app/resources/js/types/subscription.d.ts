import Addon from "./addon";
import Customer from "./customer";
import Product from "./product";
import Schedule from "./schedule";
import Service from "./service";
import SubscriptionCleaningDetail from "./subscriptionCleaningDetail";
import SubscriptionLaundryDetail from "./subscriptionLaundryDetail";
import User from "./user";

export default interface Subscription {
  id: number;
  userId: number;
  customerId: number;
  serviceId: number;
  frequency: number;
  weekday: number;
  isFixed: boolean;
  startAt: string;
  startTime: string;
  endTime: string;
  refillSequence: number;
  isPaused: boolean;
  createdAt: string;
  updatedAt: string;
  isCleaningHasLaundry: boolean;
  subscribableType?:
    | "App\\Models\\SubscriptionCleaningDetail"
    | "App\\Models\\SubscriptionLaundryDetail";
  subscribableId?: number;
  endAt?: string;
  description?: string;
  deletedAt?: string;
  totalRawPrice?: number;
  user?: User;
  fixedPriceId?: number;
  customer?: Customer;
  service?: Service;
  tasks?: CustomTask[];
  totalPrice?: number;
  fixedPrice?: FixedPrice;
  updatedSchedules?: Schedule[];
  products?: Product[];
  addons?: Addon[];
  detail?: SubscriptionCleaningDetail | SubscriptionLaundryDetail;
}
