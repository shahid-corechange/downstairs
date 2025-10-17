import City from "./city";

export default interface Address {
  id: number;
  cityId: number;
  address: string;
  area: string;
  postalCode: string;
  fullAddress: string;
  createdAt: string;
  updatedAt: string;
  address2?: string;
  accuracy?: number;
  latitude?: number;
  longitude?: number;
  deletedAt?: string;
  city?: City;
}

export interface AddressArea {
  area: string;
  postalCode: string;
}

export interface AddressGeocode {
  latitude: number;
  longitude: number;
  partialMatch: boolean;
}
