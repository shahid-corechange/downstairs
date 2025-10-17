import { Dict } from ".";
import Address from "./address";
import PropertyType from "./propertyType";
import User from "./user";

export default interface Property {
  id: number;
  addressId: number;
  typeId: number;
  membershipType: string;
  keyDescription: string;
  keyPlace: string;
  squareMeter: number;
  status: "active" | "inactive";
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
  meta?: Dict;
  users?: User[];
  address?: Address;
  type?: PropertyType;
  companyUser?: User;
  keyInformation?: KeyInformation;
}

export interface KeyInformation {
  keyPlace: string;
  frontDoorCode: string;
  alarmCodeOff: string;
  alarmCodeOn: string;
  information: string;
}
