import Schedule from "./schedule";
import TimeAdjustment from "./timeAdjustment";
import User from "./user";

export default interface ScheduleEmployee {
  id: number;
  scheduleId: number;
  workHourId: number;
  extraWorkTime: number;
  status: "pending" | "cancel" | "progress" | "done";
  totalWorkTime: number;
  timeAdjustmentHours: number;
  userId: number;
  workTime: number;
  createdAt: string;
  updatedAt: string;
  description?: string;
  endAt?: string;
  endIp?: string;
  endLatitude?: number;
  endLongitude?: number;
  startAt?: string;
  startIp?: string;
  startLatitude?: number;
  startLongitude?: number;
  deletedAt?: string;
  schedule?: Omit<Schedule, "allEmployees" | "activeEmployees">;
  user?: User;
  timeAdjustment?: Omit<TimeAdjustment, "schedule">;
}
