import Product from "./product";

export interface LaundryOrderProduct {
  id: number;
  laundryOrderId: number;
  productId: number;
  name: string;
  note: string;
  quantity: number;
  price: number;
  discount: number;
  priceWithVat: number;
  totalPriceWithVat: number;
  totalDiscountAmount: number;
  totalVatAmount: number;
  totalPriceWithDiscount: number;
  totalRut: number;
  hasRut: boolean;
  vatGroup: number;
  product?: Product;
  totalPrice: number;
  isModified: boolean;
}
