import Customer from "./customer";
import Order from "./order";
import User from "./user";

export default interface Invoice {
  id: number;
  userId: number;
  customerId: number;
  fortnoxInvoiceId: number;
  fortnoxTaxReductionId: number;
  type: string;
  category: string;
  month: number;
  year: number;
  totalGross: number;
  totalNet: number;
  totalVat: number;
  totalIncludeVat: number;
  totalRut: number;
  totalInvoiced: number;
  sentAt: string;
  dueAt: string;
  status: "open" | "created" | "cancel" | "sent" | "paid";
  createdAt: string;
  updatedAt: string;
  remark?: string;
  deletedAt?: string;
  user?: User;
  customer?: Customer;
  orders?: Order[];
}
