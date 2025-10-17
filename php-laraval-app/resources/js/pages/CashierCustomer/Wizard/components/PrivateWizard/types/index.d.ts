import { TWO_FACTOR_OPTIONS } from "@/constants/2fa";

import Country from "@/types/country";

export type CustomerWizardPageProps = {
  countries: Country[];
  dueDays: number;
  storeId: number;
};

export type AccountFormValues = {
  firstName: string;
  lastName: string;
  email: string;
  cellphone: string;
  discountPercentage: number;
  dueDays: number;
  invoiceMethod: string;
  identityNumber: string;
  timezone: string;
  language: string;
  currency: string;
  twoFactorAuth: (typeof TWO_FACTOR_OPTIONS)[number];
};

export type PrimaryAddressFormValues = {
  country: number;
  cityId: number;
  postalCode: string;
  area: string;
  address: string;
  latitude: number;
  longitude: number;
  address2?: string;
};

export type StepsValues = [AccountFormValues, PrimaryAddressFormValues];
