import { Translation } from "./translation";

export default interface LaundryPreference {
  id: number;
  name: string;
  description: string;
  price: number;
  percentage: number;
  vatGroup: number;
  priceWithVat: number;
  hours: number;
  includeHolidays: boolean;
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
  translations?: Translation[];
}
