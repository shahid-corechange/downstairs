import Address from "./address";
import { LaundryOrder } from "./laundryOrder";
import Product from "./product";
import StoreSale from "./storeSale";

export interface Store {
  id: number;
  addressId: number;
  name: string;
  companyNumber: string;
  phone: string;
  email: string;
  dialCode: string;
  formattedPhone: string;
  createdAt: string;
  updatedAt: string;
  deletedAt?: string;
  address?: Address;
  products?: StoreProduct[];
  users?: User[];
  laundryOrders?: LaundryOrder[];
  sales?: StoreSale[];
}

export interface StoreProduct {
  storeId: number;
  productId: number;
  status: "active" | "inactive";
  store?: Store;
  product?: Product;
}
