import City from "./city";

export default interface Country {
  id: number;
  code: string;
  name: string;
  currency: string;
  dialCode: string;
  cities?: City[];
}
