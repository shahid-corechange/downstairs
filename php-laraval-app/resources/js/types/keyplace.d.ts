import Property from "./property";

export default interface KeyPlace {
  id: number;
  propertyId?: number;
  property?: Property;
}
