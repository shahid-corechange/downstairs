import StoreSale from "@/types/storeSale";

export type ReceiptModalData = {
  storeSaleId: number;
};

export type SuccessPayload = {
  storeSale: StoreSale;
};

export type DirectSaleCheckoutProps = {
  storeId: number;
};

export type PaymentMethod = "cash" | "credit_card";
