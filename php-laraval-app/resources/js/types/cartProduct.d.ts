export type EditCartProductFormValues = {
  name: string;
  quantity: number;
  priceWithVat: number;
  vatGroup: number;
  discount: number;
  totalPrice: number;
  note?: string;
};

export type CartProductModalData = {
  index: number;
  key: keyof EditCartProductFormValues;
  product: CartProduct;
  cartProducts?: CartProduct[];
};

export interface CartProduct {
  id: number;
  name: string;
  quantity: number;
  hasRut: boolean;
  priceWithVat: number;
  vatGroup: number;
  discount: number;
  totalPrice: number;
  isModified: boolean;
  isFixedPrice: boolean;
  isLaundryPreference?: boolean;
  note?: string;
}
