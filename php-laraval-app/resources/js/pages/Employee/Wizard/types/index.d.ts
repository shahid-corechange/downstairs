import { TWO_FACTOR_OPTIONS } from "@/constants/2fa";

export type EmployeeWizardPageProps = {
  countries: Country[];
  roles: string[];
};

export type AccountFormValues = {
  firstName: string;
  lastName: string;
  email: string;
  cellphone: string;
  identityNumber: string;
  timezone: string;
  language: string;
  currency: string;
  twoFactorAuth: (typeof TWO_FACTOR_OPTIONS)[number];
  roles: string;
};

export type AddressFormValues = {
  country: number;
  cityId: number;
  postalCode: string;
  area: string;
  address: string;
  latitude: number;
  longitude: number;
};

export type StepsValues = [AccountFormValues, AddressFormValues];
