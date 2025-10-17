import Product from "./product";
import { Store } from "./store";
import User from "./user";

export default interface StoreSale {
  id: number;
  storeId: number;
  causerId: number;
  status: string;
  paymentMethod: string;
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
  totalPriceWithVat: number;
  totalPriceWithDiscount: number;
  totalDiscount: number;
  totalVat: StoreSaleVat;
  totalToPay: number;
  roundAmount: number;
  roundedTotalToPay: number;
  meta?: Record<string, string | number | boolean | null>;
  store?: Store;
  causer?: User;
  products?: StoreSaleProduct[];
}

export interface StoreSaleProduct {
  id: number;
  storeSaleId: number;
  productId: number;
  name: string;
  note: string;
  quantity: number;
  price: number;
  vatGroup: number;
  discount: number;
  priceWithVat: number;
  discountAmount: number;
  vatAmount: number;
  priceWithDiscount: number;
  createdAt: string;
  updatedAt: string;
  storeSale?: StoreSale;
  product?: Product;
}

export interface StoreSaleVat {
  [key: number]: number;
}
