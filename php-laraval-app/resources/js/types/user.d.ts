import { Role } from "./authorization";
import Customer from "./customer";
import CustomerDiscount from "./customerDiscount";
import Employee from "./employee";
import Property from "./property";
import Subscription from "./subscription";

export default interface User {
  id: number;
  firstName: string;
  lastName: string;
  emailVerifiedAt: string;
  cellphoneVerifiedAt: string;
  identityNumber: string;
  identityNumberVerifiedAt: string;
  lastSeen: string;
  createdAt: string;
  updatedAt: string;
  fullname: string;
  initials: string;
  status:
    | "active"
    | "inactive"
    | "suspended"
    | "deleted"
    | "pending"
    | "blocked";
  permissions: string[];
  totalCredits: number;
  email?: string;
  cellphone?: string;
  formattedCellphone?: string;
  deletedAt?: string;
  info?: UserInfo;
  properties?: Property[];
  roles?: Role[];
  subscriptions?: Subscription[];
  customers?: Customer[];
  employee?: Employee;
  laundryDiscounts?: CustomerDiscount[];
  cleaningDiscounts?: CustomerDiscount[];
}

export interface UserInfo {
  language: string;
  timezone: string;
  currency: string;
  notificationMethod: "email" | "sms" | "app";
  twoFactorAuth: "email" | "sms" | "disabled";
  marketing: number;
  avatar?: string;
}
