import User from "./user";

export default interface Credit {
  id: number;
  userId: number;
  remainingAmount: number;
  type: "refund" | "granted";
  description: string;
  validUntil: string;
  isSystemCreated: boolean;
  createdAt: string;
  updatedAt: string;
  scheduleId?: number;
  schedule?: Schedule;
  transactions?: CreditTransaction[];
  issuer?: User;
}

export interface CreditTransaction {
  id: number;
  creditId: number;
  scheduleId: number;
  type: string;
  totalAmount: number;
  description: string;
  createdAt: string;
  updatedAt: string;
}
