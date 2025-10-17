export type ChangedWorkers = {
  scheduleEmployeeId: number;
  scheduleId: number;
  userId: number;
};

export type WorkerCollisionError = {
  scheduleWorkerIds: number[];
  scheduleCollidedWorkers: ScheduleEmployee[];
  workerCollisions: ScheduleEmployee[];
};
