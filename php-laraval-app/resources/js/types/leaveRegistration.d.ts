import Employee from "./employee";

export default interface LeaveRegistration {
  id: number;
  employeeId: number;
  type: string;
  startAt: string;
  endAt: string;
  isStopped: boolean;
  isPaused: boolean;
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
  rescheduleNeeded: boolean;
  employee?: Employee;
}
