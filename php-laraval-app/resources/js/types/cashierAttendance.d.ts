import { Store } from "./store";
import TimeReport from "./timeReport";
import User from "./user";

export default interface CashierAttendance {
  id: number;
  userId: number;
  storeId: number;
  workHourId: number;
  checkInAt: string;
  checkOutAt: string;
  checkInCauserId: number;
  checkOutCauserId: number;
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
  totalHours: number;
  user?: User;
  store?: Store;
  workHour?: TimeReport;
  checkInCauser?: User;
  checkOutCauser?: User;
}
