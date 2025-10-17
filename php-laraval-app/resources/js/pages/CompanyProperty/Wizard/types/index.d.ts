export type CompanyPropertyWizardPageProps = {
  countries: Country[];
  companies: User[];
  propertyTypes: PropertyType[];
};

export type AddressFormValues = {
  userId: number;
  country: number;
  cityId: number;
  postalCode: string;
  area: string;
  address: string;
  latitude: number;
  longitude: number;
};

export type PropertyFormValues = {
  propertyTypeId: number;
  squareMeter: number;
  note?: string;
};

export type KeyFormValues = {
  keyPlace: string;
  frontDoorCode: string;
  alarmCodeOff: string;
  alarmCodeOn: string;
  information: string;
};

export type StepsValues = [
  AddressFormValues,
  PropertyFormValues,
  KeyFormValues,
];
