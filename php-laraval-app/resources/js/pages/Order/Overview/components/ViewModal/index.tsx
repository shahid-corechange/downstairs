import { Flex, Spinner, Tab, TabList, TabPanels, Tabs } from "@chakra-ui/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import Modal from "@/components/Modal";
import { ModalExpansion } from "@/components/Modal/types";

import { useGetOrder } from "@/services/order";

import AttendanceRecordPanel from "./components/AttendanceRecordPanel";
import FixedPricePanel from "./components/FixedPricePanel";
import InfoPanel from "./components/InfoPanel";
import RowPanel from "./components/RowPanel";

interface ViewModalProps {
  isOpen: boolean;
  onClose: () => void;
  orderId?: number;
  extraArticleIds: number[];
}

const ViewModal = ({
  orderId,
  extraArticleIds,
  isOpen,
  onClose,
}: ViewModalProps) => {
  const { t } = useTranslation();
  const [isExpanded, setIsExpanded] = useState(false);
  const [expandableContent, setExpandableContent] = useState<React.ReactNode>();
  const [expandableTitle, setExpandableTitle] = useState<string>();
  const [activeTabIndex, setActiveTabIndex] = useState(0);

  const order = useGetOrder(orderId, {
    request: {
      include: [
        "rows",
        "user",
        "customer.address",
        "service",
        "subscription.products.product",
        "fixedPrice.rows",
        "fixedPrice.meta",
        "schedule.activeEmployees.user",
      ],
      only: [
        "id",
        "paidBy",
        "paidAt",
        "status",
        "orderedAt",
        "orderableType",
        "rows.id",
        "rows.description",
        "rows.fortnoxArticleId",
        "rows.priceWithVat",
        "rows.quantity",
        "rows.unit",
        "rows.vat",
        "rows.hasRut",
        "rows.isServiceRow",
        "rows.isMaterialRow",
        "rows.discountPercentage",
        "rows.internalNote",
        "user.fullname",
        "customer.name",
        "customer.membershipType",
        "customer.address.fullAddress",
        "service.name",
        "service.fortnoxArticleId",
        "subscription.products.product.fortnoxArticleId",
        "fixedPrice.isPerOrder",
        "fixedPrice.rows.id",
        "fixedPrice.rows.type",
        "fixedPrice.rows.priceWithVat",
        "fixedPrice.rows.vatGroup",
        "fixedPrice.rows.hasRut",
        "fixedPrice.meta",
        "schedule.activeEmployees.user.fullname",
        "schedule.activeEmployees.startAt",
        "schedule.activeEmployees.endAt",
      ],
    },
  });

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
      isOpen={isOpen}
      isExpanded={isExpanded}
      expandableTitle={expandableTitle}
      expandableContent={expandableContent}
      onClose={handleClose}
      onShrink={handleShrink}
    >
      {!order.isFetching && order.data ? (
        <Tabs index={activeTabIndex} onChange={handleChangeTab}>
          <TabList>
            <Tab>{t("information")}</Tab>
            <Tab>{t("rows")}</Tab>
            {order.data.fixedPrice && <Tab>{t("fixed prices")}</Tab>}
            {order.data.schedule?.activeEmployees && (
              <Tab>{t("attendance record")}</Tab>
            )}
          </TabList>

          <TabPanels>
            <InfoPanel order={order.data} py={8} />
            <RowPanel
              order={order.data}
              extraArticleIds={extraArticleIds}
              onModalExpansion={handleModalExpansion}
              onModalShrink={handleShrink}
              onRefetch={() => order.refetch()}
              py={8}
            />
            {order.data.fixedPrice && (
              <FixedPricePanel order={order.data} py={8} />
            )}
            {order.data.schedule?.activeEmployees && (
              <AttendanceRecordPanel
                data={order.data.schedule?.activeEmployees}
                py={8}
              />
            )}
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
