export type AddCustomCartProductModalData = {
  products?: CartProduct[];
  quantity: number;
};

export type AddCustomCartProductFormValues = {
  name: string;
  priceWithVat: number;
  hasRut: boolean;
  vatGroup: number;
  quantity: number;
  discount: number;
  totalPrice: number;
  note?: string;
};
