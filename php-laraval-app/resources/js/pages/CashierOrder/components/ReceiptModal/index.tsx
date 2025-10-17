import { Tab, TabList, TabPanels, Tabs } from "@chakra-ui/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import Modal from "@/components/Modal";

import { LaundryOrder } from "@/types/laundryOrder";

import CustomerPanel from "./components/CustomerReceiptPanel";
import LaundryPanel from "./components/LaundryReceiptPanel";

interface ReceiptModalProps {
  laundryOrder?: LaundryOrder;
  isOpen: boolean;
  onClose: () => void;
}

const ReceiptModal = ({ laundryOrder, isOpen, onClose }: ReceiptModalProps) => {
  const { t } = useTranslation();
  const [activeTabIndex, setActiveTabIndex] = useState<number>(0);

  const handleChangeTab = (index: number) => {
    setActiveTabIndex(index);
  };

  const handleClose = () => {
    onClose();
    setActiveTabIndex(0);
  };

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
