import Customer from "./customer";
import OrderFixedPrice from "./orderFixedPrice";
import Schedule from "./schedule";
import Service from "./service";
import { Store } from "./store";
import Subscription from "./subscription";
import User from "./user";

export default interface Order {
  id: number;
  userId: number;
  customerId: number;
  serviceId: number;
  subscriptionId: number;
  invoiceId: number;
  orderableId: number;
  orderableType: string;
  status: "draft" | "progress" | "cancel" | "done";
  paidBy: string;
  paidAt?: string;
  orderedAt: string;
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
  user?: User;
  customer?: Customer;
  service?: Service;
  rows?: OrderRow[];
  schedule?: Schedule;
  fixedPrice?: OrderFixedPrice;
  subscription?: Subscription;
  total?: string;
  store?: Store;
  pickupAt?: string;
}

export interface OrderRow {
  id: number;
  fortnoxArticleId: string;
  orderId: number;
  quantity: number;
  price: number;
  priceWithVat: number;
  vat: number;
  hasRut: boolean;
  isServiceRow: boolean;
  isMaterialRow: boolean;
  description: string;
  unit: string;
  createdAt: string;
  updatedAt: string;
  discountPercentage?: number;
  internalNote?: string;
  order?: Order;
}
