import { Tab, TabList, TabPanels, Tabs } from "@chakra-ui/react";
import { useCallback, useState } from "react";
import { useTranslation } from "react-i18next";

import Modal from "@/components/Modal";

import { useGetLaundryOrder } from "@/services/laundryOrder";

import CustomerPanel from "./components/CustomerReceiptPanel";
import LaundryPanel from "./components/LaundryReceiptPanel";

interface ReceiptModalProps {
  laundryOrderId?: number;
  isOpen: boolean;
  onClose: () => void;
}

const ReceiptModal = ({
  laundryOrderId,
  isOpen,
  onClose,
}: ReceiptModalProps) => {
  const { t } = useTranslation();
  const [activeTabIndex, setActiveTabIndex] = useState<number>(0);

  const { data: laundryOrder } = useGetLaundryOrder(laundryOrderId, {
    request: {
      include: [
        "user",
        "causer",
        "customer",
        "store",
        "pickupTeam",
        "deliveryTeam",
        "products",
        "laundryPreference",
      ],
      only: [
        "id",
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
      ],
    },
  });

  const handleChangeTab = useCallback((index: number) => {
    setActiveTabIndex(index);
  }, []);

  const handleClose = useCallback(() => {
    onClose();
    setActiveTabIndex(0);
  }, [onClose]);

  if (!laundryOrder) {
    return;
  }

  return (
    <Modal
      bodyContainer={{ p: 8 }}
      isOpen={isOpen}
      onClose={handleClose}
      size="lg"
      aria-label={t("receipt modal")}
    >
      <Tabs index={activeTabIndex} onChange={handleChangeTab}>
        <TabList>
          <Tab>{t("laundry receipt")}</Tab>
          <Tab>{t("customer receipt")}</Tab>
        </TabList>

        <TabPanels maxH="container.sm" overflow="auto">
          <LaundryPanel laundryOrder={laundryOrder} onClose={onClose} pt={8} />
          <CustomerPanel laundryOrder={laundryOrder} onClose={onClose} pt={8} />
        </TabPanels>
      </Tabs>
    </Modal>
  );
};

export default ReceiptModal;
