import Address from "./address";

export default interface Employee {
  id: number;
  addressId: number;
  userId: number;
  fortnoxId: string;
  identityNumber: string;
  name: string;
  email: string;
  phone1: string;
  formattedPhone1: string;
  isValidIdentity: boolean;
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
  meta?: Record<string, unknown>;
  address?: Address;
}
