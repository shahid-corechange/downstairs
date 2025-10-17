import CashierAttendance from "./cashierAttendance";
import ScheduleEmployee from "./scheduleEmployee";
import User from "./user";

export default interface TimeReport {
  id: number;
  userId: number;
  fortnoxAttendanceId: number;
  type: string;
  date: string;
  startTime: string;
  endTime: string;
  workHours: number;
  timeAdjustmentHours: number;
  totalHours: number;
  unapprovedHours: number;
  bookingHours: number;
  hasDeviation: boolean;
  user?: User;
  schedules?: ScheduleEmployee[];
  attendances?: CashierAttendance[];
}
