import ScheduleEmployee from "./scheduleEmployee";
import User from "./user";

export default interface TimeAdjustment {
  id: number;
  scheduleEmployeeId: number;
  causerId: number;
  quarters: number;
  reason: string;
  createdAt: string;
  updatedAt: string;
  schedule?: ScheduleEmployee;
  causer?: User;
}
