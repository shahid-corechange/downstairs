import { Flex, useConst } from "@chakra-ui/react";
import { Head, router } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";
import {
  AiOutlineCalendar,
  AiOutlineCheckCircle,
  AiOutlineCreditCard,
  AiOutlineEye,
  AiOutlineFileText,
} from "react-icons/ai";

import BrandText from "@/components/BrandText";
import DataTable from "@/components/DataTable";

import { PaymentMethod } from "@/constants/paymentMethod";

import { usePageModal } from "@/hooks/modal";

import CashierLayout from "@/layouts/Cashier";

import ChangeScheduleModal, {
  ChangeScheduleModalData,
} from "@/pages/CashierOrder/components/ChangeScheduleModal";
import ChangeStatusModal from "@/pages/CashierOrder/components/ChangeStatusModal";
import ReceiptModal from "@/pages/CashierOrder/components/ReceiptModal";

import { useGetCashierOrders } from "@/services/cashierOrder";

import { LaundryOrder } from "@/types/laundryOrder";
import LaundryPreference from "@/types/laundryPreference";
import { PagePagination } from "@/types/pagination";
import User from "@/types/user";

import { compareLaundryOrderStatus, getNextStatus } from "@/utils/laundryOrder";
import { RequestQueryStringOptions } from "@/utils/request";

import { PageProps } from "@/types";

import getColumns from "./column";

type CashierCustomerOrderPageProps = {
  customer: User;
  laundryPreferences: LaundryPreference[];
};

const CashierCustomerOrderPage = ({
  customer,
  laundryPreferences,
  storeId,
}: PageProps<CashierCustomerOrderPageProps>) => {
  const { t } = useTranslation();

  const columns = useConst(getColumns(t));

  const [requestOptions, setRequestOptions] = useState<
    Partial<RequestQueryStringOptions<LaundryOrder>>
  >({});

  const { modalData, modal, openModal, closeModal } = usePageModal<
    LaundryOrder | ChangeScheduleModalData,
    "changeSchedule" | "receipt" | "changeStatus" | "payment"
  >();

  const {
    data: customerOrders,
    isFetching,
    refetch,
  } = useGetCashierOrders({
    request: {
      ...requestOptions,
      include: [
        "user",
        "causer",
        "customer",
        "store",
        "pickupTeam",
        "deliveryTeam",
        "products",
        "laundryPreference",
        "pickupInCleaning",
        "deliveryInCleaning",
      ],
      only: [
        "id",
        "customerId",
        "userId",
        "totalRut",
        "totalPriceWithVat",
        "totalPriceWithDiscount",
        "totalDiscount",
        "totalVat",
        "totalToPay",
        "roundAmount",
        "preferenceAmount",
        "orderedAt",
        "dueAt",
        "paidAt",
        "paymentMethod",
        "status",
        "createdAt",
        "updatedAt",
        "deletedAt",
        "laundryPreferenceId",
        "pickupInCleaningId",
        "deliveryInCleaningId",
        "user.id",
        "user.fullname",
        "user.formattedCellphone",
        "causer.id",
        "causer.fullname",
        "customer.address.fullAddress",
        "customer.address.postalCode",
        "products.name",
        "products.quantity",
        "products.price",
        "products.discount",
        "products.priceWithVat",
        "products.totalPriceWithVat",
        "products.totalDiscountAmount",
        "products.totalVatAmount",
        "products.totalPriceWithDiscount",
        "products.totalRut",
        "products.hasRut",
        "products.vatGroup",
        "store.id",
        "store.name",
        "laundryPreference.name",
        "laundryPreference.price",
        "laundryPreference.percentage",
        "pickupInCleaning.startAt",
        "deliveryInCleaning.startAt",
      ],
      filter: {
        ...requestOptions.filter,
        eq: {
          userId: customer.id,
          storeId: storeId ?? undefined,
        },
      },
      sort: {
        id: "desc",
      },
      pagination: "page",
      size: -1,
    },
    query: {
      enabled: !!storeId && !!customer.id,
    },
  });

  const handleCardPayment = (laundryOrder: LaundryOrder) => {
    router.get(
      `/cashier/customers/${laundryOrder?.user?.id}/orders/${laundryOrder?.id}/card-payment`,
    );
  };

  const handleInvoicePayment = (laundryOrder: LaundryOrder) => {
    router.get(
      `/cashier/customers/${laundryOrder?.user?.id}/orders/${laundryOrder?.id}/invoice-payment`,
    );
  };

  return (
    <>
      <Head>
        <title>{t("customer orders")}</title>
      </Head>
      <CashierLayout content={{ p: 4 }} customerId={customer.id}>
        <Flex direction="column">
          <BrandText text={t("customer orders")} />
        </Flex>

        <DataTable
          title={t("customer orders")}
          data={customerOrders?.data ?? []}
          columns={columns}
          fetchFn={(options) => setRequestOptions(options)}
          isFetching={isFetching}
          pagination={customerOrders?.pagination as PagePagination}
          actions={[
            {
              label: t("view"),
              icon: AiOutlineEye,
              onClick: (row) =>
                router.get(
                  `/cashier/customers/${row.original.user?.id}/orders/${row.original.id}`,
                ),
            },
            (row) => ({
              isHidden: (row) => row.original.status !== "pending",
              label: t("change schedule"),
              icon: AiOutlineCalendar,
              onClick: () =>
                openModal("changeSchedule", {
                  laundryOrder: row.original,
                  laundryPreferences,
                }),
            }),
            (row) => ({
              isHidden: (row) =>
                compareLaundryOrderStatus(
                  "after",
                  "delivered",
                  row.original.status,
                ),
              label: t("laundry order marker", {
                status: t(getNextStatus(row.original.status)),
              }),
              icon: AiOutlineCheckCircle,
              onClick: () => openModal("changeStatus", row.original),
            }),
            (row) => ({
              isHidden: (row) =>
                row.original.status !== "done" ||
                row.original.paymentMethod === PaymentMethod.INVOICE,
              label: t("pay with card"),
              icon: AiOutlineCreditCard,
              onClick: () => handleCardPayment(row.original),
            }),
            (row) => ({
              isHidden: (row) => row.original.status !== "done",
              label: t("pay with invoice"),
              icon: AiOutlineFileText,
              onClick: () => handleInvoicePayment(row.original),
            }),
            {
              label: t("receipt"),
              icon: AiOutlineFileText,
              onClick: (row) => openModal("receipt", row.original),
            },
          ]}
          useWindowScroll
          serverSide
        />
      </CashierLayout>

      <ChangeScheduleModal
        data={modalData as ChangeScheduleModalData}
        isOpen={modal === "changeSchedule" && !!modalData}
        onClose={closeModal}
        onRefetch={refetch}
      />
      <ReceiptModal
        laundryOrder={modalData as LaundryOrder}
        isOpen={modal === "receipt" && !!modalData}
        onClose={closeModal}
      />
      <ChangeStatusModal
        data={modalData as LaundryOrder}
        isOpen={modal === "changeStatus" && !!modalData}
        onClose={closeModal}
        onRefetch={refetch}
      />
    </>
  );
};

export default CashierCustomerOrderPage;
