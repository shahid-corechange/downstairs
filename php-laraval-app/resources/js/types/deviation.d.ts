import { DEVIATION_TYPES } from "@/constants/deviation";

import Schedule from "./schedule";
import User from "./user";

export default interface Deviation {
  id: number;
  userId: number;
  scheduleId: number;
  type: (typeof DEVIATION_TYPES)[number];
  reason: string;
  isHandled: boolean;
  createdAt: string;
  updatedAt: string;
  user?: User;
  schedule?: Schedule;
}

export interface Type {
  id: number;
  name: string;
  createdAt: string;
  updatedAt: string;
}
