import { Role } from "@/types/authorization";
import User from "@/types/user";

export type EmployeeProps = {
  employees: User[];
  roles: Role[];
};
