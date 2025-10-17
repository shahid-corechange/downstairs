import { CartProduct } from "@/types/cartProduct";
import FixedPrice from "@/types/fixedPrice";
import { LaundryOrder } from "@/types/laundryOrder";
import LaundryPreference from "@/types/laundryPreference";
import User from "@/types/user";

export type CheckoutFormWithScheduleType = {
  laundryPreferenceId: number;
  userId: number;
  isPickupScheduled: number;
  isDeliveryScheduled: number;
  orderedAt: string;
  pickupPropertyId: number;
  pickupTime: string;
  pickupTeamId: number;
  deliveryPropertyId: number;
  deliveryTime: string;
  deliveryTeamId: number;
  products: CartProduct[];
  sendMessage: boolean;
  message: string;
};

export type CheckoutFormType = {
  laundryPreferenceId: number;
  userId: number;
  pickupScheduleId?: number;
  deliveryScheduleId?: number;
  products: CartProduct[];
  sendMessage: boolean;
  message: string;
};

export type SuccessPayload = {
  laundryOrder: LaundryOrder;
};

export type CashierCustomerOrderDetailsProps = {
  customer: User;
  laundryOrder: LaundryOrder;
  laundryPreferences: LaundryPreference[];
  fixedPrice: FixedPrice;
};
