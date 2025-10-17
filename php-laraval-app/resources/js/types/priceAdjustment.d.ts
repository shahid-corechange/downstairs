import User from "./user";

export interface PriceAdjustmentRow {
  id: number;
  adjustableId: number;
  adjustableName: string;
  adjustableType: string;
  status: "done" | "pending";
  priceAdjustmentId: number;
  price: number;
  previousPrice: number;
  vatGroup: number;
  priceWithVat: number;
  previousPriceWithVat: number;
  createdAt: string;
  updatedAt: string;
  detail: string;
  priceAdjustment: string;
}

export default interface PriceAdjustment {
  id: number;
  causerId: number;
  type: "service" | "addon" | "product" | "fixed_price";
  status: "done" | "pending" | "partial";
  description: string;
  priceType:
    | "fixed_price_with_vat"
    | "dynamic_percentage"
    | "dynamic_fixed_with_vat";
  price: number;
  executionDate: string;
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
  causer?: User;
  rows?: PriceAdjustmentRow[];
}
