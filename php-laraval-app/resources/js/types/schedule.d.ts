import { TaskFormValues } from "@/components/TaskForm/types";

import Addon from "./addon";
import CustomTask from "./customTask";
import Customer from "./customer";
import Product from "./product";
import Property from "./property";
import ScheduleCleaning from "./scheduleCleaning";
import ScheduleEmployee from "./scheduleEmployee";
import ScheduleLaundry from "./scheduleLaundry";
import Service from "./service";
import Subscription from "./subscription";
import Team from "./team";
import Translations from "./translation";
import User from "./user";

export default interface Schedule {
  id: number;
  userId: number;
  serviceId: number;
  customerId?: number;
  subscriptionId: number;
  propertyId: number;
  scheduleableType: string;
  scheduleableId: number;
  status:
    | "draft"
    | "pending"
    | "cancel"
    | "progress"
    | "booked"
    | "change"
    | "done"
    | "invoiced";
  startAt: string;
  endAt: string;
  originalStartAt: string;
  quarters: number;
  isFixed: boolean;
  keyInformation?: string;
  hasDeviation: boolean;
  createdAt: string;
  updatedAt: string;
  teamId?: number;
  workStatus?: "ok" | "started late" | "ended late" | "not started";
  actualStartAt?: string;
  actualEndAt?: string;
  actualQuarters?: number;
  note?: string;
  canceledAt?: string;
  cancelableId?: number;
  canceledBy?: string;
  canceledType?: "customer" | "admin" | "employee";
  deletedAt?: string;
  notes?: ScheduleNote;
  user?: User;
  service?: Service;
  team?: Team;
  customer?: Customer;
  subscription?: Subscription;
  property?: Property;
  refund?: ScheduleRefundInfo;
  items?: ScheduleItem[];
  changeRequest?: ScheduleChangeRequest;
  tasks?: CustomTask[];
  allEmployees?: ScheduleEmployee[];
  activeEmployees?: ScheduleEmployee[];
  scheduleTasks?: ScheduleTask[];
  addonSummaries?: ScheduleItemSummary[];
  cancelable?: Customer | User | Team;
  detail?: Partial<ScheduleCleaning | ScheduleLaundry>;
}

export interface ScheduleItem {
  id: number;
  scheduleId: number;
  itemableId: number;
  itemableType: string;
  price: number;
  quantity: number;
  discountPercentage: number;
  createdAt: string;
  updatedAt: string;
  paymentMethod: "invoice" | "credit";
  schedule?: Omit<Schedule, "items">;
  item?: Addon | Product;
}

export interface ScheduleNote {
  propertyNote?: string;
  subscriptionNote?: string;
  note?: string;
}

export interface ScheduleChangeRequest {
  id: number;
  scheduleId: number;
  causerId: number;
  canReschedule: boolean;
  status: "pending" | "approved" | "rejected";
  createdAt: string;
  updatedAt: string;
  deletedAt?: string;
  originalStartAt?: string;
  startAtChanged?: string;
  originalEndAt?: string;
  endAtChanged?: string;
  schedule?: Omit<Schedule, "changeRequest">;
  causer?: User;
}

export interface ScheduleTask {
  id: number;
  name: string;
  description: string;
  source?: "schedule" | "subscription" | "add on" | "product" | "service";
  isCompleted?: boolean;
  translations?: Translations;
}

export type ScheduleItemSummary = ScheduleItem & {
  name: string;
  isCharge: boolean;
};

export interface ScheduleRefundInfo {
  amount: number;
  validUntil: string;
}

export type ScheduleSummation = {
  type: string;
  amount: number;
  size?: number;
  unit?: string;
};

export type ReschedulePayload = {
  scheduleId: number;
  startAt: string;
  teamId: number;
  isNotify: boolean;
};

export type CancelSchedulePayload = {
  scheduleId: number;
  refund: boolean;
};

export type EditSchedulePayload = {
  scheduleId: number;
  teamId?: number;
  startAt: string;
  endAt: string;
  removeAddOns: number[];
  newAddOns: {
    addonId: number;
    quantity: number;
    useCredit: boolean;
  }[];
  note?: string;
};

export type AddScheduleWorkersPayload = {
  scheduleId: number;
  workerIds: number[];
};

export type ChangeScheduleWorkerStatusPayload = {
  scheduleId: number;
  userId: number;
  action: "enable" | "disable";
};

export type RemoveScheduleWorkerPayload = {
  scheduleId: number;
  userId: number;
};

export type RevertScheduleWorkerPayload = {
  scheduleId: number;
  userId: number;
};

export type AddScheduleTaskPayload = {
  scheduleId: number;
} & TaskFormValues;

export type EditScheduleTaskPayload = {
  scheduleId: number;
  taskId: number;
} & TaskFormValues;

export type DeleteScheduleTaskPayload = {
  scheduleId: number;
  taskId: number;
};

export type CreateHistoricalSchedulePayload = {
  userId: number;
  customerId: number;
  propertyId: number;
  serviceId: number;
  teamId: number;
  quarters: number;
  description: string;
  startAt: string;
  startTimeAt: string;
  addonIds: number[];
  totalPrice?: number;
  workers?: {
    userId: number;
    startAt: string;
    endAt: string;
  }[];
};

export type BulkChangeWorkersPayload = {
  changes: {
    scheduleId: number;
    scheduleEmployeeId: number;
    userId: number;
  }[];
};
