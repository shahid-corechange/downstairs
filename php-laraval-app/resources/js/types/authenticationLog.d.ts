import User from "./user";

export default interface AuthenticationLog {
  id: number;
  userId: number;
  ipAddress: string;
  userAgent: string;
  loginAt: string;
  loginSuccessful: boolean;
  logoutAt: string;
  clearedByUser: boolean;
  user?: User;
  location?: AuthenticationLogLocation;
}

export interface AuthenticationLogLocation {
  id: number;
  ip: string;
  lat: number;
  lon: number;
  city: string;
  state: string;
  cached: boolean;
  country: string;
  isoCode: string;
  timezone: string;
  continent: string;
  stateName: string;
  postalCode: string;
}
