import Customer from "@/types/customer";

export type CompanyPageProps = {
  companies: Customer[];
};

export type ViewModalPageProps = {
  dueDays: number;
  creditExpirationDays: number;
  creditRefundTimeWindow: number;
};
