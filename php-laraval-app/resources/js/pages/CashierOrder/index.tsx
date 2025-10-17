import { Flex, useConst } from "@chakra-ui/react";
import { Head, router } from "@inertiajs/react";
import dayjs, { Dayjs } from "dayjs";
import { useMemo, useState } from "react";
import { useTranslation } from "react-i18next";
import {
  AiOutlineCalendar,
  AiOutlineCheckCircle,
  AiOutlineCreditCard,
  AiOutlineEye,
  AiOutlineFileText,
} from "react-icons/ai";

import DataTable from "@/components/DataTable";
import WeeklyDatePicker, {
  WeeklyDateOrder,
} from "@/components/WeeklyDatePicker";

import { PaymentMethod } from "@/constants/paymentMethod";

import { usePageModal } from "@/hooks/modal";

import CashierLayout from "@/layouts/Cashier";

import { useGetCashierOrders } from "@/services/cashierOrder";

import { LaundryOrder } from "@/types/laundryOrder";
import LaundryPreference from "@/types/laundryPreference";

import { toDayjs } from "@/utils/datetime";
import { compareLaundryOrderStatus, getNextStatus } from "@/utils/laundryOrder";

import { PageProps } from "@/types";

import getColumns from "./column";
import ChangeScheduleModal, {
  ChangeScheduleModalData,
} from "./components/ChangeScheduleModal";
import ChangeStatusModal from "./components/ChangeStatusModal";
import ReceiptModal from "./components/ReceiptModal";

type CashierOrderOverviewPageProps = {
  laundryPreferences: LaundryPreference[];
};

const CashierOrderOverviewPage = ({
  laundryPreferences,
  storeId,
}: PageProps<CashierOrderOverviewPageProps>) => {
  const { t } = useTranslation();

  const columns = useConst(getColumns(t));

  const [selectedDate, setSelectedDate] = useState<Dayjs>(toDayjs());
  const [startDate, setStartDate] = useState<Dayjs>(toDayjs().startOf("week"));
  const [endDate, setEndDate] = useState<Dayjs>(toDayjs().endOf("week"));

  const { modalData, modal, openModal, closeModal } = usePageModal<
    LaundryOrder | ChangeScheduleModalData,
    "changeSchedule" | "receipt" | "changeStatus" | "payment"
  >();

  const {
    data: cashierOrders,
    isFetching,
    refetch,
  } = useGetCashierOrders({
    request: {
      filter: {
        eq: {
          storeId: storeId ?? undefined,
        },
      },
      orFilters: [
        {
          gte: { createdAt: toDayjs(startDate).toISOString() },
          lte: { createdAt: toDayjs(endDate).toISOString() },
          eq: { createdAt: toDayjs(selectedDate).toISOString() },
        },
      ],
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
        "userId",
        "customerId",
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
      sort: {
        id: "desc",
      },
      pagination: "page",
      size: -1,
    },
    query: {
      enabled: !!storeId && !!startDate && !!endDate,
      refetchOnMount: true,
    },
  });

  const weeklyOrders: WeeklyDateOrder[] = useMemo(() => {
    if (!cashierOrders?.data) {
      return [];
    }

    return cashierOrders.data.reduce((acc: WeeklyDateOrder[], order) => {
      const date = dayjs(order.createdAt);
      const existing = acc.find((item) => item.date.isSame(date, "day"));

      if (existing) {
        existing.count += 1;
        existing.amount += Number(order.totalToPay || 0);
      } else {
        acc.push({
          date,
          count: 1,
          amount: Number(order.totalToPay || 0),
        });
      }

      return acc;
    }, []);
  }, [cashierOrders?.data]);

  const selectedDateOrders: LaundryOrder[] = useMemo(() => {
    if (!cashierOrders?.data) {
      return [];
    }

    return cashierOrders.data.filter((item) =>
      dayjs(item.createdAt).isSame(selectedDate, "day"),
    );
  }, [cashierOrders?.data, selectedDate]);

  const handleDateSelect = (date: Dayjs): void => {
    setSelectedDate(date);
  };

  const handleWeekChange = (startDate: Dayjs, endDate: Dayjs): void => {
    setStartDate(startDate);
    setEndDate(endDate);
  };

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
        <title>{t("orders")}</title>
      </Head>
      <CashierLayout content={{ p: 4 }}>
        <Flex direction="column" gap={12}>
          <WeeklyDatePicker
            onDateSelect={handleDateSelect}
            onWeekChange={handleWeekChange}
            data={weeklyOrders}
            isLoading={isFetching}
          />

          <DataTable
            title={t("orders")}
            data={selectedDateOrders}
            columns={columns}
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
                    userId: row.original.userId,
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
            searchable={false}
            isLoading={isFetching}
            useWindowScroll
          />
        </Flex>
      </CashierLayout>

      <ChangeScheduleModal
        data={modalData as ChangeScheduleModalData}
        isOpen={modal === "changeSchedule" && !!modalData}
        onRefetch={refetch}
        onClose={closeModal}
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

export default CashierOrderOverviewPage;
