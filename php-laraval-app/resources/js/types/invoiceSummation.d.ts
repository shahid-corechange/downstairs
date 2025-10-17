export default interface InvoiceSummation {
  invoicePeriod: string;
  sentPeriod: string;
  totalGross: number;
  totalNet: number;
  totalVat: number;
  totalRut: number;
  totalIncludeVat: number;
  totalInvoiced: number;
  invoiceCount: number;
}
