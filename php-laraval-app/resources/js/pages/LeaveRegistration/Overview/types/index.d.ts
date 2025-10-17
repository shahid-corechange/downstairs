import LeaveRegistration from "@/types/leaveRegistration";

export type EmployeeList = {
  id: number;
  name: string;
};

export type LeaveRegistrationProps = {
  leaveRegistrations: LeaveRegistration[];
  employees: EmployeeList[];
};
