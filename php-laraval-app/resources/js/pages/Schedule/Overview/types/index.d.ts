import Addon from "@/types/addon";
import Product from "@/types/product";
import Schedule from "@/types/schedule";
import ScheduleEmployee from "@/types/scheduleEmployee";
import Team from "@/types/team";

export type ScheduleFilterStatus =
  | "booked"
  | "progress"
  | "done"
  | "cancel"
  | "active"
  | "all";

export type ScheduleOverviewPageProps = {
  schedules: Schedule[];
  teams: Team[];
  addons: Addon[];
  products: Product[];
  transportPrice: number;
  materialPrice: number;
  defaultShownTeamIds: number[];
  defaultMinHourShow: string;
  defaultMaxHourShow: string;
  creditRefundTimeWindow: number;
  subscriptionRefillSequence: number;
};

export type WorkerCollisionError = {
  scheduleWorkerIds: number[];
  scheduleCollidedWorkers: ScheduleEmployee[];
  workerCollisions: ScheduleEmployee[];
};

export interface ScheduleCellInfo {
  teamId: number;
  dayIndex: number;
  time: string;
  duration: number;
}
