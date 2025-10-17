import { Dict } from ".";
import User from "./user";

export default interface ActivityLog {
  id: number;
  logName: string;
  description: string;
  subjectType: string;
  event: string;
  subjectId: number;
  causerType: string;
  causerId: number;
  batchUuid: string;
  createdAt: string;
  updateAt: string;
  properties?: ActivityLogProperties;
  user?: User;
}

export interface ActivityLogProperties {
  old: Dict;
  attributes: Dict;
}
