import Customer from "./customer";
import { LaundryOrderHistory } from "./laundryOrderHistory";
import { LaundryOrderProduct } from "./laundryOrderProduct";
import { LaundryOrderSchedule } from "./laundryOrderSchedule";
import LaundryPreference from "./laundryPreference";
import Property from "./property";
import Schedule from "./schedule";
import Subscription from "./subscription";
import User from "./user";

export interface LaundryOrder {
  id: number;
  invoiceId: number;
  storeId: number;
  laundryPreferenceId: number;
  userId: number;
  causerId: number;
  customerId: number;
  pickupPropertyId: number;
  pickupTeamId: number;
  pickupTime: string;
  deliveryPropertyId: number;
  deliveryTeamId: number;
  deliveryTime: string;
  orderedAt: string;
  paidAt: string;
  status:
    | "pending"
    | "in_progress_pickup"
    | "picked_up"
    | "in_progress_store"
    | "in_progress_laundry"
    | "in_progress_delivery"
    | "delivered"
    | "done"
    | "paid"
    | "closed";
  customerType: "private" | "company";
  dueAt: string;
  totalRut: number;
  totalPriceWithVat: number;
  totalPriceWithDiscount: number;
  totalDiscount: number;
  totalVat: LaundryOrderVat;
  totalToPay: number;
  roundAmount: number;
  preferenceAmount: number;
  createdAt: string;
  updatedAt: string;
  paymentMethod: string;
  subscriptionId?: number;
  pickupInCleaningId?: number;
  deliveryInCleaningId?: number;
  orderSource?: string;
  deletedAt?: string;
  invoice?: Invoice;
  store?: Store;
  user?: User;
  causer?: User;
  customer?: Customer;
  subscription?: Subscription;
  laundryPreference?: LaundryPreference;
  pickupProperty?: Property;
  pickupTeam?: Team;
  deliveryProperty?: Property;
  deliveryTeam?: Team;
  pickupInCleaning?: Schedule;
  deliveryInCleaning?: Schedule;
  schedules?: LaundryOrderSchedule[];
  histories?: LaundryOrderHistory[];
  products?: LaundryOrderProduct[];
}

export interface LaundryOrderVat {
  "25": number;
  "12": number;
}
