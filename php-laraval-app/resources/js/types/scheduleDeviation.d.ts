import Schedule from "./schedule";

export default interface ScheduleDeviation {
  id: number;
  scheduleId: number;
  types: string[];
  isHandled: boolean;
  createdAt: string;
  updatedAt: string;
  meta?: ScheduleDeviationMeta;
  schedule?: Schedule;
}

export interface ScheduleDeviationMeta {
  actualQuarters?: number;
  items?: { id: number; isCharge: boolean }[];
}
