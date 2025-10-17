import Country from "./country";

export default interface City {
  id: number;
  countryId: number;
  name: string;
  country?: Country;
}
