import { Dict } from ".";
import Subscription from "./subscription";

export default interface OrderFixedPrice {
  id: number;
  isPerOrder: boolean;
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
  meta?: Dict;
  subscriptions?: Subscription[];
  rows?: OrderFixedPriceRow[];
}

export interface OrderFixedPriceRow {
  id: number;
  fixedPriceId: string;
  type: string;
  quantity: number;
  price: number;
  priceWithVat: number;
  vatGroup: number;
  hasRut: boolean;
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
  description?: string;
}
