import Property from "./property";
import Team from "./team";

export default interface SubscriptionCleaningDetail {
  id: number;
  propertyId: number;
  teamId: number;
  quarters: number;
  startTime: string;
  endTime: string;
  teamName: string;
  address: string;
  property?: Property;
  team?: Team;
}
