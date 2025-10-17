import { TFunction } from "i18next";

import Invoice from "@/types/invoice";
import Order from "@/types/order";
import OrderFixedPrice from "@/types/orderFixedPrice";

import { toDayjs } from "@/utils/datetime";

import { InvoiceRow } from "./types";

export const getInvoiceSummary = (rows: InvoiceRow[], invoice?: Invoice) => {
  const result = {
    gross: 0,
    net: 0,
    vat: 0,
    totalIncludeVat: 0,
    taxReductionBasis: 0,
    taxReduction: 0,
    totalInvoiced: 0,
    roundOff: 0,
    totalIncludeVatNoRounded: 0,
  };

  if (!invoice) {
    return result;
  }

  for (const row of rows) {
    const gross = row.price * row.quantity;
    const net = gross * (1 - row.discountPercentage / 100);
    const vat = (net * row.vat) / 100;
    const taxReductionBasis = row.hasRut ? net + vat : 0;

    result.gross += gross;
    result.net += net;
    result.vat += vat;
    result.taxReductionBasis += taxReductionBasis;
  }

  const taxReductionPercentage = invoice.type === "laundry" ? 0.25 : 0.5;
  const baseTaxReduction = result.taxReductionBasis * taxReductionPercentage;

  result.totalIncludeVat = Math.round(result.net + result.vat);
  result.totalIncludeVatNoRounded = result.net + result.vat;
  // if invoice type is laundry, we don't need to floor the tax reduction
  result.taxReduction =
    invoice.type === "laundry"
      ? baseTaxReduction
      : Math.floor(baseTaxReduction);
  result.totalInvoiced = result.totalIncludeVat - result.taxReduction;
  result.roundOff = result.totalIncludeVat - result.totalIncludeVatNoRounded;

  return result;
};

export const getFixedPriceArticleIds = (
  order: Order,
  transportArticleId: string,
  materialArticleId: string,
) => {
  if (!order.fixedPrice || !order.subscription || !order.service) {
    return [];
  }

  const articleIds = [transportArticleId, materialArticleId];

  if (order.service.fortnoxArticleId) {
    articleIds.push(order.service.fortnoxArticleId);
  }

  for (const product of order.subscription.products ?? []) {
    if (product.fortnoxArticleId) {
      articleIds.push(product.fortnoxArticleId);
    }
  }

  return articleIds;
};

export const createFixedPriceRows = (
  t: TFunction,
  orderFixedPrice: OrderFixedPrice,
  isReadonly: boolean,
) => {
  const rows: InvoiceRow[] = [];

  for (const row of orderFixedPrice.rows ?? []) {
    const description = row.description ?? t(row.type) + " " + t("fixed price");

    const invoiceRow = {
      key: `${row.id}`,
      parentId: orderFixedPrice.id,
      id: row.id,
      type: "fixed price" as const,
      fortnoxArticleId: "",
      description,
      quantity: row.quantity,
      unit: "st",
      price: row.price,
      discountPercentage: 0,
      vat: row.vatGroup,
      hasRut: row.hasRut,
      isReadonly,
      isHeader: false,
    };

    rows.push(invoiceRow);
  }

  return rows;
};

export const createOrderHeaderRows = (t: TFunction, order: Order) => {
  const rows: InvoiceRow[] = [];
  const orderDate = toDayjs(order.orderedAt).format("YYYY-MM-DD");

  rows.push({
    key: order.schedule && !order.fixedPrice ? `${order.id}` : `#${order.id}`,
    type: "order" as const,
    parentId: order.id,
    fortnoxArticleId: "",
    description: `${t("order")} ${orderDate} (#${order.id})`,
    quantity: 0,
    unit: "",
    price: 0,
    discountPercentage: 0,
    vat: 0,
    hasRut: false,
    isReadonly: true,
    isHeader: true,
  });

  if (order.schedule && !order.fixedPrice) {
    const property = order?.schedule?.property?.address?.address;
    const propertyId = order?.schedule?.property?.id;

    rows.push({
      key: `#${order.id}-${propertyId}`,
      type: "order" as const,
      parentId: order.id,
      fortnoxArticleId: "",
      description: `${t("property")} (#${propertyId}): ${property}`,
      quantity: 0,
      unit: "",
      price: 0,
      discountPercentage: 0,
      vat: 0,
      hasRut: false,
      isReadonly: true,
      isHeader: true,
    });
  }

  return rows;
};

export const createSeparatorRow = () => ({
  key: "separator",
  type: "separator" as const,
  fortnoxArticleId: "",
  description: "----------------------------------------",
  quantity: 0,
  unit: "",
  price: 0,
  discountPercentage: 0,
  vat: 0,
  hasRut: false,
  isReadonly: true,
  isHeader: true,
});

export const getNewRowSpecs = (rows: InvoiceRow[], index: number) => {
  const currentRow = rows[index];
  const nextRow = rows[index + 1];

  if (currentRow.type === "fixed price" || nextRow?.type === "fixed price") {
    return {
      type: "fixed price" as const,
      parentId:
        currentRow.type === "fixed price"
          ? currentRow.parentId
          : nextRow?.parentId,
    };
  }

  return {
    type: "order" as const,
    parentId: currentRow.parentId,
  };
};
