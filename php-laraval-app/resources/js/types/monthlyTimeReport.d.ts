import Employee from "./employee";

export default interface MonthlyTimeReport {
  userId: number;
  fullname: string;
  month: number;
  year: number;
  totalWorkHours: number;
  adjustmentHours: number;
  totalHours: number;
  bookingHours: number;
  hasDeviation: boolean;
  fortnoxId?: string;
  employeeId: number;
  employee: Employee;
}
