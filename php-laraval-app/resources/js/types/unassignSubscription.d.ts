import Customer from "./customer";
import Product from "./product";
import Service from "./service";
import SubscriptionCleaningDetail from "./subscriptionCleaningDetail";
import SubscriptionLaundryDetail from "./subscriptionLaundryDetail";
import User from "./user";

export default interface UnassignSubscription {
  id: number;
  userId: number;
  customerId: number;
  serviceId: number;
  frequency: number;
  weekday: number;
  quarters: number;
  propertyAddress: string;
  isFixed: boolean;
  startAt: string;
  startTime: string;
  endTime: string;
  createdAt: string;
  updatedAt: string;
  deletedAt?: string;
  endAt?: string;
  description?: string;
  totalPrice?: number;
  totalRawPrice?: number;
  fixedPrice?: number;
  user?: User;
  customer?: Customer;
  service?: Service;
  tasks?: CustomTask[];
  productIds?: number[];
  products?: Product[];
  addons?: Addon[];
  cleaningDetail?: SubscriptionCleaningDetail;
  laundryDetail?: SubscriptionLaundryDetail;
}
