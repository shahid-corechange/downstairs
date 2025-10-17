import { Dayjs } from "dayjs";

import Service from "@/types/service";
import Team from "@/types/team";
import User from "@/types/user";

export type WizardRequestPayload = {
  userId: number;
  customerId: number;
  serviceId: number;
  addonIds: number[];
  description: string;
  isFixed: boolean;
  frequency: number;
  startAt: string;
  endAt: string | null;
  type?: "private" | "company";
  products?: number[];
  totalPrice?: number;
  fixedPrice?: number;
  fixedPriceId?: number;
  cleaningDetail?: CleaningDetailPayload;
  laundryDetail?: LaundryDetailPayload;
};

export type CleaningDetailPayload = {
  propertyId: number;
  quarters: number;
  startTime: string;
  endTime?: string;
  teamId?: number;
};

export type LaundryDetailPayload = {
  storeId: number;
  laundryPreferenceId: number;
  pickupPropertyId?: number;
  pickupTeamId?: number;
  pickupTime?: string;
  deliveryPropertyId?: number;
  deliveryTeamId?: number;
  deliveryTime?: string;
};

export type CompanySubscriptionWizardPageProps = {
  users: User[];
  services: Service[];
  frequencies: Record<string, string>;
  teams: Team[];
  transportPrice: number;
  materialPrice: number;
};

export type PlanFormValues = {
  userId: number;
  customerId: number;
  propertyId: number;
  teamId: number;
  serviceId: number;
  addonIds: string;
  quarters: number;
  description: string;
  totalPrice: number;
  calculatedPrice: number;
  fixedPriceId: number;
};

export type TimeFormValues = {
  isFixed: string;
  frequency: number;
  startAt: string;
  endAt: string;
  startTimeAt: string;
  endTimeAt: string;
  utcStartAt: Dayjs;
};

export type StepsValues = [PlanFormValues, TimeFormValues];
