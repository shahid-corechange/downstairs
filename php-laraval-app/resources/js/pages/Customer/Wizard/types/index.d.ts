import { TWO_FACTOR_OPTIONS } from "@/constants/2fa";

export type CustomerWizardPageProps = {
  countries: Country[];
  dueDays: number;
};

export type AccountFormValues = {
  firstName: string;
  lastName: string;
  email: string;
  cellphone: string;
  dueDays: number;
  invoiceMethod: string;
  identityNumber: string;
  timezone: string;
  language: string;
  currency: string;
  twoFactorAuth: (typeof TWO_FACTOR_OPTIONS)[number];
  notificationMethod?: string;
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

export type InvoiceAddressFormValues = {
  invoiceCountry: number;
  invoiceCityId: number;
  invoicePostalCode: string;
  invoiceArea: string;
  invoiceAddress: string;
  invoiceLatitude: number;
  invoiceLongitude: number;
};

export type PropertyFormValues = {
  squareMeter: number;
  keyPlace: string;
  frontDoorCode: string;
  alarmCodeOff: string;
  alarmCodeOn: string;
  information: string;
  note?: string;
};

export type StepsValues = [
  AccountFormValues,
  PrimaryAddressFormValues,
  // InvoiceAddressFormValues, - InvoiceAddress step is temporarily disabled until UI adjustment
  PropertyFormValues,
];
