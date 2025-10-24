import Address from "./address";
import User from "./user";

export default interface Customer {
  id: number;
  addressId: number;
  membershipType: "private" | "company";
  type: string;
  identityNumber: string;
  name: string;
  email: string;
  phone1: string;
  formattedPhone1: string;
  isFull: boolean;
  dueDays: number;
  invoiceMethod: string;
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
  fortnoxId?: string;
  customerRefId?: number;
  reference?: string;
  meta?: Record<string, unknown>;
  address?: Address;
  companyUser?: User;
  users?: User[];
}
