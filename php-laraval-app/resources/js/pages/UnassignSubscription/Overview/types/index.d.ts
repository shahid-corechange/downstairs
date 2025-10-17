import { Dayjs } from "dayjs";

import Team from "@/types/team";
import UnassignSubscription from "@/types/unassignSubscription";

export type UnassignSubscriptionPageProps = {
  unassignSubscriptions: UnassignSubscription[];
  frequencies: Record<string, string>;
  transportPrice: number;
  materialPrice: number;
  sort: Record<string, "asc" | "desc"> | undefined;
  teams: Team[];
};

export type PlanFormValues = {
  userId: number;
  customerId: number;
  propertyId: number;
  serviceId: number;
  // productCarts?: number[]; // TODO: uncomment this when product is implemented
  addonIds: string;
  quarters: number;
  description: string;
  fixedPrice: number;
  calculatedPrice: number;
};

export type CreateUnassignSubscriptionPayload = {
  type: "private" | "company";
  userId: number;
  customerId: number;
  serviceId: number;
  productCarts?: number[];
  addonIds: number[];
  description: string;
  isFixed: boolean;
  frequency: number;
  startAt: string;
  endAt: string | null;
  fixedPrice?: number;
  cleaningDetail?: CleaningDetailPayload;
  laundryDetail?: LaundryDetailPayload;
};

export type CleaningDetailPayload = {
  quarters: number;
  startTime: string;
  propertyId?: number;
  endTime?: string;
  teamId?: number;
};

export type LaundryDetailPayload = {
  storeId: number;
  laundryPreferenceId: number;
  pickupAddressId: number;
  pickupTeamId?: number;
  pickupTime: string;
  deliveryAddressId?: number;
  deliveryTeamId?: number;
  deliveryTime: string;
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
