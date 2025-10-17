import {
  FIXED_PRICE_ROW_TYPES,
  FIXED_PRICE_TYPES,
} from "@/constants/fixedPrice";

import Subscription from "./subscription";
import User from "./user";

export default interface FixedPrice {
  id: number;
  userId: number;
  type: (typeof FIXED_PRICE_TYPES)[number];
  isPerOrder: boolean;
  isIncludeLaundry: boolean;
  isActive: boolean;
  hasActiveSubscriptions: boolean;
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
  startDate?: string;
  endDate?: string;
  user?: User;
  subscriptions?: Subscription[];
  rows?: FixedPriceRow[];
  laundryProducts?: Product[];
}

export interface FixedPriceRow {
  id: number;
  fixedPriceId: number;
  type: (typeof FIXED_PRICE_ROW_TYPES)[number];
  quantity: number;
  price: number;
  priceWithVat: number;
  vatGroup: number;
  hasRut: boolean;
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
}
