export interface InvoiceRow {
  key: string;
  type: "fixed price" | "order" | "separator";
  fortnoxArticleId: string;
  description: string;
  quantity: number;
  unit: string;
  price: number;
  discountPercentage: number;
  vat: number;
  hasRut: boolean;
  isReadonly: boolean;
  isHeader: boolean;
  parentId?: number;
  id?: number;
}
