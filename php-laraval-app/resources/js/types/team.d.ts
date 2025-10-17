import User from "./user";

export default interface Team {
  id: number;
  name: string;
  color: string;
  description: string;
  isActive: boolean;
  totalWorkers: number;
  avatar?: string;
  deletedAt?: string;
  users?: User[];
}
