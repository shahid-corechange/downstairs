import { Dayjs } from "dayjs";

import User from "@/types/user";

export type PlanFormValues = {
  userId: number;
  customerId: number;
  propertyId: number;
  serviceId: number;
  addonIds: string;
  quarters: number;
  description: string;
  totalPrice: number;
  calculatedPrice: number;
  startAt: string;
  endAt: string;
  startTimeAt: string;
  endTimeAt: string;
  utcStartAt: Dayjs;
  utcEndAt: Dayjs;
};

export type TimeFormValues = Record<string, never>;

export type WorkerAttendance = {
  userId: number;
  startAt: string;
  endAt: string;
};

export type WorkerFormValues = {
  attendances: WorkerAttendance[];
  workers: User[];
};

export type StepsValues = [PlanFormValues, WorkerFormValues, TimeFormValues];
