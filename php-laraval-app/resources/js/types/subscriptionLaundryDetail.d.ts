import LaundryPreference from "./laundryPreference";
import Property from "./property";
import { Store } from "./store";
import Team from "./team";

export default interface SubscriptionLaundryDetail {
  id: number;
  storeId: number;
  laundryPreferenceId: number;
  pickupPropertyId: number;
  pickupTeamId: number;
  pickupTime: string;
  deliveryPropertyId: number;
  deliveryTeamId: number;
  deliveryTime: string;
  startTime: string;
  endTime: string;
  teamName: string;
  address: string;
  quarters: number;
  store?: Store;
  laundryPreference?: LaundryPreference;
  pickupProperty?: Property;
  pickupTeam?: Team;
  deliveryProperty?: Property;
  deliveryTeam?: Team;
}
