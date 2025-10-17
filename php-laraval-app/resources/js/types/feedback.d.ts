import User from "./user";

export default interface Feedback {
  id: number;
  userId: number;
  option: string;
  description: string;
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
  user?: User;
}
