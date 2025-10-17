import Service from "./service";

export default interface ServiceQuarter {
  id: number;
  serviceId: number;
  minSquareMeters: number;
  maxSquareMeters: number;
  quarters: number;
  hours: number;
  createdAt: string;
  updatedAt: string;
  deletedAt: string;
  service?: Service;
}
