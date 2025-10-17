import { TWO_FACTOR_OPTIONS } from "@/constants/2fa";

import Country from "@/types/country";
import PropertyType from "@/types/propertyType";

export type CompanyWizardPageProps = {
  countries: Country[];
  propertyTypes: PropertyType[];
  dueDays: number;
};

export type AccountFormValues = {
  companyName: string;
  orgNumber: string;
  companyEmail: string;
  companyPhone: string;
  dueDays: number;
  invoiceMethod: string;
  language: string;
  timezone: string;
  currency: string;
  twoFactorAuth: (typeof TWO_FACTOR_OPTIONS)[number];
};

export type ContactFormValues = {
  firstName: string;
  lastName: string;
  email?: string;
  cellphone?: string;
  identityNumber?: string;
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

export type StepsValues = [
  AccountFormValues,
  ContactFormValues,
  PrimaryAddressFormValues,
];
