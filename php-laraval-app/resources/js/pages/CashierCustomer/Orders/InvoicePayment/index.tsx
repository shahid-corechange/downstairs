import {
  Button,
  Card,
  CardBody,
  CardHeader,
  Flex,
  Heading,
} from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { Head, router } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import BrandText from "@/components/BrandText";

import { PaymentMethod } from "@/constants/paymentMethod";
import { ServiceMembershipType } from "@/constants/service";

import { usePageModal } from "@/hooks/modal";
import useCart from "@/hooks/useCart";

import CashierLayout from "@/layouts/Cashier";

import useAuthStore from "@/stores/auth";

import { LaundryOrder } from "@/types/laundryOrder";
import User from "@/types/user";

import { formatCurrency } from "@/utils/currency";

import { PageProps } from "@/types";

import InfoRow from "./components/InfoRow";
import ReceiptModal from "./components/ReceiptModal";

type InvoicePaymentPageProps = {
  laundryOrder: LaundryOrder;
  customer: User;
};

type SuccessPayload = {
  laundryOrder: LaundryOrder;
};

const InvoicePayment = ({
  laundryOrder,
  customer,
}: PageProps<InvoicePaymentPageProps>) => {
  const { t } = useTranslation();
  const { language, currency } = useAuthStore();

  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isPaid, setIsPaid] = useState(!!laundryOrder.paidAt);

  const { clearCart } = useCart();

  const { modalData, modal, openModal, closeModal } = usePageModal<
    LaundryOrder,
    "receipt"
  >();

  const amount =
    laundryOrder.totalToPay +
    laundryOrder.preferenceAmount +
    laundryOrder.roundAmount;

  const goToOrderDetails = () => {
    router.get(`/cashier/customers/${customer.id}/orders/${laundryOrder.id}`);
  };

  const handlePay = () => {
    setIsSubmitting(true);

    const payload = {
      paymentMethod: PaymentMethod.INVOICE,
    };

    router.post(`/cashier/orders/${laundryOrder?.id}/pay`, payload, {
      onFinish: () => setIsSubmitting(false),
      onSuccess: (page) => {
        const {
          flash: { successPayload },
        } = (
          page as Page<
            PageProps<
              Record<string, unknown>,
              SuccessPayload | undefined,
              unknown
            >
          >
        ).props;

        const { laundryOrder } = successPayload ?? {};

        if (laundryOrder) {
          const cartKey = {
            userId: laundryOrder.userId,
            storeId: laundryOrder.storeId,
            laundryOrderId: laundryOrder.id,
          };

          clearCart(cartKey);
          openModal("receipt", laundryOrder);
          setIsPaid(true);
        }
      },
    });
  };

  return (
    <>
      <Head>
        <title>{t("invoice payment")}</title>
      </Head>
      <CashierLayout content={{ p: 4 }} customerId={customer.id}>
        <Flex direction="column" mb={8}>
          <BrandText text={t("payment")} />
        </Flex>

        <Card w="50%">
          <CardHeader>
            <Heading size="sm">{t("order info")}</Heading>
          </CardHeader>
          <CardBody fontSize="sm">
            <Flex direction="column" gap={4}>
              <InfoRow
                label={t("amount")}
                value={formatCurrency(language, currency, amount || 0, 2)}
              />
              <InfoRow
                label={t("rut")}
                value={formatCurrency(
                  language,
                  currency,
                  laundryOrder?.totalRut || 0,
                  2,
                )}
              />
              <InfoRow
                label={t("customer type")}
                value={t(laundryOrder?.customer?.membershipType ?? "-")}
              />
              <InfoRow
                label={t("identity number")}
                value={laundryOrder?.customer?.identityNumber}
              />
              {laundryOrder?.customer?.membershipType ===
              ServiceMembershipType.PRIVATE ? (
                <>
                  <InfoRow
                    label={t("first name")}
                    value={laundryOrder?.user?.firstName}
                  />
                  <InfoRow
                    label={t("last name")}
                    value={laundryOrder?.user?.lastName}
                  />
                </>
              ) : (
                <InfoRow
                  label={t("company name")}
                  value={laundryOrder?.customer?.name}
                />
              )}
              <InfoRow
                label={t("address")}
                value={laundryOrder?.customer?.address?.fullAddress}
              />
              <InfoRow
                label={t("postal code")}
                value={laundryOrder?.customer?.address?.postalCode}
              />
              <InfoRow
                label={t("phone")}
                value={laundryOrder?.customer?.formattedPhone1}
              />
              <InfoRow label={t("email")} value={laundryOrder?.user?.email} />
              <InfoRow
                label={t("invoice type")}
                value={t(laundryOrder?.customer?.invoiceMethod ?? "-")}
              />

              <Flex justify="flex-end" gap={4} mt={4}>
                {isPaid ? (
                  <>
                    <Button
                      colorScheme="gray"
                      fontSize="sm"
                      onClick={() => openModal("receipt", laundryOrder)}
                    >
                      {t("receipt")}
                    </Button>
                    <Button
                      colorScheme="brand"
                      fontSize="sm"
                      onClick={goToOrderDetails}
                    >
                      {t("back to order")}
                    </Button>
                  </>
                ) : (
                  <>
                    <Button
                      colorScheme="red"
                      fontSize="sm"
                      onClick={goToOrderDetails}
                    >
                      {t("cancel")}
                    </Button>
                    <Button
                      colorScheme="brand"
                      fontSize="sm"
                      onClick={handlePay}
                      isLoading={isSubmitting}
                    >
                      {t("complete payment")}
                    </Button>
                  </>
                )}
              </Flex>
            </Flex>
          </CardBody>
        </Card>
      </CashierLayout>

      <ReceiptModal
        isOpen={modal === "receipt" && !!modalData}
        onClose={closeModal}
        laundryOrder={modalData}
      />
    </>
  );
};

export default InvoicePayment;
