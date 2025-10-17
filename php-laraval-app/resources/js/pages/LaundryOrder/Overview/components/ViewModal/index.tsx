import { Flex, Spinner, Tab, TabList, TabPanels, Tabs } from "@chakra-ui/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import Modal from "@/components/Modal";
import { ModalExpansion } from "@/components/Modal/types";

import { useGetLaundryOrder } from "@/services/laundryOrder";

import HistoryPanel from "./components/HistoryPanel";
import InfoPanel from "./components/InfoPanel";
import ProductPanel from "./components/ProductPanel";
import SchedulePanel from "./components/SchedulePanel";

interface ViewModalProps {
  isOpen: boolean;
  onClose: () => void;
  laundryOrderId?: number;
}

const ViewModal = ({ isOpen, onClose, laundryOrderId }: ViewModalProps) => {
  const { t } = useTranslation();
  const [isExpanded, setIsExpanded] = useState(false);
  const [expandableContent, setExpandableContent] = useState<React.ReactNode>();
  const [expandableTitle, setExpandableTitle] = useState<string>();
  const [activeTabIndex, setActiveTabIndex] = useState(0);

  const laundryOrder = useGetLaundryOrder(laundryOrderId);

  const handleModalExpansion = (expansion: ModalExpansion) => {
    setIsExpanded(true);
    setExpandableContent(expansion.content);
    setExpandableTitle(expansion.title);
  };

  const handleShrink = () => {
    setIsExpanded(false);
    setExpandableContent(undefined);
    setExpandableTitle(undefined);
  };

  const handleChangeTab = (index: number) => {
    setActiveTabIndex(index);
    handleShrink();
  };

  const handleClose = () => {
    handleShrink();
    onClose();
    setActiveTabIndex(0);
  };

  return (
    <Modal
      bodyContainer={{ p: 8 }}
      expandableSize="lg"
      size="5xl"
      isOpen={isOpen}
      isExpanded={isExpanded}
      expandableTitle={expandableTitle}
      expandableContent={expandableContent}
      onClose={handleClose}
      onShrink={handleShrink}
    >
      {!laundryOrder.isFetching && laundryOrder.data ? (
        <Tabs index={activeTabIndex} onChange={handleChangeTab}>
          <TabList>
            <Tab>{t("information")}</Tab>
            <Tab>{t("products")}</Tab>
            <Tab>{t("schedules")}</Tab>
            <Tab>{t("history")}</Tab>
          </TabList>

          <TabPanels>
            <InfoPanel laundryOrder={laundryOrder.data} />
            <ProductPanel
              laundryOrder={laundryOrder.data}
              onModalExpansion={handleModalExpansion}
              onModalShrink={handleShrink}
              onRefetch={laundryOrder.refetch}
            />
            <SchedulePanel
              laundryOrder={laundryOrder.data}
              onModalExpansion={handleModalExpansion}
              onModalShrink={handleShrink}
              onRefetch={laundryOrder.refetch}
            />
            <HistoryPanel laundryOrder={laundryOrder.data} />
          </TabPanels>
        </Tabs>
      ) : (
        <Flex h="xs" alignItems="center" justifyContent="center">
          <Spinner size="md" />
        </Flex>
      )}
    </Modal>
  );
};

export default ViewModal;
