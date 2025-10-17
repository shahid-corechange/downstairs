import User from "./user";

export interface RutCoApplicant {
  id: number;
  userId: number;
  identityNumber: string;
  name: string;
  phone: string;
  dialCode: string;
  formattedPhone: string;
  isEnabled: boolean;
  isPaused: boolean;
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
  pauseStartDate?: string;
  pauseEndDate?: string;
  user?: User;
}
