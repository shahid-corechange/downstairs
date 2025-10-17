import { Flex, Spinner, Tab, TabList, TabPanels, Tabs } from "@chakra-ui/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import Modal from "@/components/Modal";
import { ModalExpansion } from "@/components/Modal/types";

import { useGetCompanyFixedPrice } from "@/services/companyFixedPrice";

import RowPanel from "./components/RowPanel";
import SubscriptionPanel from "./components/SubscriptionPanel";

interface ViewModalProps {
  isOpen: boolean;
  onClose: () => void;
  fixedPriceId?: number;
  onRefetch?: () => void;
}

const ViewModal = ({
  fixedPriceId,
  isOpen,
  onClose,
  onRefetch,
}: ViewModalProps) => {
  const { t } = useTranslation();
  const [isExpanded, setIsExpanded] = useState(false);
  const [expandableContent, setExpandableContent] = useState<React.ReactNode>();
  const [expandableTitle, setExpandableTitle] = useState<string>();
  const [activeTabIndex, setActiveTabIndex] = useState(0);

  const fixedPrice = useGetCompanyFixedPrice(fixedPriceId, {
    request: {
      include: [
        "subscriptions.service",
        "subscriptions.detail.team",
        "subscriptions.detail.property.address.city.country",
        "subscriptions.detail.pickupTeam",
        "subscriptions.detail.pickupProperty.address.city.country",
        "subscriptions.detail.deliveryTeam",
        "subscriptions.detail.deliveryProperty.address.city.country",
        "rows",
        "laundryProducts",
      ],
      only: [
        "id",
        "userId",
        "type",
        "isPerOrder",
        "startDate",
        "endDate",
        "subscriptions.id",
        "subscriptions.frequency",
        "subscriptions.weekday",
        "subscriptions.startTime",
        "subscriptions.endTime",
        "subscriptions.service.name",
        "subscriptions.detail.teamName",
        "subscriptions.detail.address",
        "rows.id",
        "rows.type",
        "rows.quantity",
        "rows.priceWithVat",
        "rows.vatGroup",
        "rows.hasRut",
        "laundryProducts.id",
        "laundryProducts.name",
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

  const handleRefetch = () => {
    fixedPrice.refetch();
    onRefetch?.();
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
      {!fixedPrice.isFetching && fixedPrice.data ? (
        <Tabs index={activeTabIndex} onChange={handleChangeTab}>
          <TabList>
            <Tab>{t("subscriptions")}</Tab>
            <Tab>{t("rows")}</Tab>
          </TabList>

          <TabPanels>
            <SubscriptionPanel
              fixedPrice={fixedPrice.data}
              py={8}
              onRefetch={handleRefetch}
            />
            <RowPanel
              fixedPrice={fixedPrice.data}
              onModalExpansion={handleModalExpansion}
              onModalShrink={handleShrink}
              onRefetch={handleRefetch}
              py={8}
            />
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
