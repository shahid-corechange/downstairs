import {
  Box,
  Flex,
  Spinner,
  Tab,
  TabList,
  TabPanels,
  Tabs,
} from "@chakra-ui/react";
import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";

import AuthorizationGuard from "@/components/AuthorizationGuard";
import Modal from "@/components/Modal";
import { ModalExpansion } from "@/components/Modal/types";

import { useGetCompanyAddresses } from "@/services/company";

import User from "@/types/user";

import CreditPanel from "./components/CreditPanel";
import DiscountPanel from "./components/DiscountPanel";
import FixedPricePanel from "./components/FixedPricePanel";
import InvoicePanel from "./components/InvoicePanel";
import PrimaryPanel from "./components/PrimaryPanel";
import PropertyPanel from "./components/PropertyPanel";
import ScheduleHistoryPanel from "./components/ScheduleHistory";
import SchedulePanel from "./components/SchedulePanel";

interface ViewModalProps {
  isOpen: boolean;
  onClose: () => void;
  companyId?: number;
  user?: User;
  activeTab?: number;
}

const ViewModal = ({
  companyId,
  user,
  isOpen,
  activeTab = 0,
  onClose,
}: ViewModalProps) => {
  const { t } = useTranslation();
  const [isExpanded, setIsExpanded] = useState(false);
  const [expandableContent, setExpandableContent] = useState<React.ReactNode>();
  const [expandableTitle, setExpandableTitle] = useState<string>();
  const [activeTabIndex, setActiveTabIndex] = useState(activeTab);
  const [userData, setUserData] = useState<User | undefined>(user);

  const customers = useGetCompanyAddresses(companyId, {
    request: {
      include: ["address.city.country", "users.info"],
      only: [
        "id",
        "customerRefId",
        "reference",
        "identityNumber",
        "phone1",
        "formattedPhone1",
        "email",
        "type",
        "name",
        "membershipType",
        "dueDays",
        "invoiceMethod",
        "deletedAt",
        "address.address",
        "address.address2",
        "address.fullAddress",
        "address.postalCode",
        "address.latitude",
        "address.longitude",
        "address.cityId",
        "address.city.name",
        "address.city.countryId",
        "address.city.country.name",
        "users.info.notificationMethod",
      ],
    },
    query: {
      enabled: !!companyId && isOpen,
    },
  });

  const handleModalExpansion = (expansion: ModalExpansion) => {
    setIsExpanded(true);
    setExpandableContent(expansion.content);
    setExpandableTitle(expansion.title);
  };

  const handleTitleUpdate = (title: string) => {
    setExpandableTitle(title);
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

  const handleUserDataUpdate = (updatedUser: User) => {
    setUserData(updatedUser);
  };

  // Update local userData when user prop changes
  useEffect(() => {
    if (user) {
      setUserData(user);
    }
  }, [user]);

  return (
    <Modal
      bodyContainer={{ p: 8 }}
      expandableContent={expandableContent}
      expandableTitle={expandableTitle}
      expandableSize={[4, 5].includes(activeTabIndex) ? "2xl" : "lg"}
      isExpanded={isExpanded}
      isOpen={isOpen}
      onClose={handleClose}
      onShrink={handleShrink}
      size="full"
    >
      {companyId && userData && !customers.isFetching && customers.data ? (
        <Tabs index={activeTabIndex} onChange={handleChangeTab}>
          <Box overflow="auto">
            <TabList minW="fit-content">
              <AuthorizationGuard permissions="companies primary address read">
                <Tab>{t("primary")}</Tab>
              </AuthorizationGuard>
              <AuthorizationGuard permissions="company invoice addresses index">
                <Tab>{t("invoice")}</Tab>
              </AuthorizationGuard>
              <AuthorizationGuard permissions="company schedules index">
                <Tab>{t("schedules")}</Tab>
              </AuthorizationGuard>
              <AuthorizationGuard permissions="company schedule histories index">
                <Tab>{t("schedule history")}</Tab>
              </AuthorizationGuard>
              <AuthorizationGuard permissions="company credits index">
                <Tab>{t("credits")}</Tab>
              </AuthorizationGuard>
              <AuthorizationGuard permissions="company discounts index">
                <Tab>{t("discounts")}</Tab>
              </AuthorizationGuard>
              <AuthorizationGuard permissions="company fixed prices index">
                <Tab>{t("fixed prices")}</Tab>
              </AuthorizationGuard>
              <AuthorizationGuard permissions="company properties index">
                <Tab>{t("properties")}</Tab>
              </AuthorizationGuard>
            </TabList>
          </Box>
          <TabPanels>
            <AuthorizationGuard permissions="companies primary address read">
              <PrimaryPanel
                companyId={companyId}
                userData={userData}
                customers={customers.data}
                onModalExpansion={handleModalExpansion}
                onModalShrink={handleShrink}
                onRefetch={() => customers.refetch()}
                onUserDataUpdate={handleUserDataUpdate}
                onTitleUpdate={handleTitleUpdate}
                py={8}
              />
            </AuthorizationGuard>
            <AuthorizationGuard permissions="company invoice addresses index">
              <InvoicePanel
                companyId={companyId}
                customers={customers.data}
                onModalExpansion={handleModalExpansion}
                onModalShrink={handleShrink}
                onRefetch={() => customers.refetch()}
                py={8}
              />
            </AuthorizationGuard>
            <AuthorizationGuard permissions="company schedules index">
              <SchedulePanel
                customers={customers.data}
                onRefetch={() => customers.refetch()}
                py={8}
              />
            </AuthorizationGuard>
            <AuthorizationGuard permissions="company schedule histories index">
              <ScheduleHistoryPanel customers={customers.data} py={8} />
            </AuthorizationGuard>
            <AuthorizationGuard permissions="company credits index">
              <CreditPanel
                userId={userData.id}
                onModalExpansion={handleModalExpansion}
                onModalShrink={handleShrink}
                py={8}
              />
            </AuthorizationGuard>
            <AuthorizationGuard permissions="company discounts index">
              <DiscountPanel
                data={userData}
                onModalExpansion={handleModalExpansion}
                onModalShrink={handleShrink}
                py={8}
              />
            </AuthorizationGuard>
            <AuthorizationGuard permissions="company fixed prices index">
              <FixedPricePanel
                data={userData}
                onModalExpansion={handleModalExpansion}
                onModalShrink={handleShrink}
                py={8}
              />
            </AuthorizationGuard>
            <AuthorizationGuard permissions="properties index">
              <PropertyPanel userId={userData.id} py={8} />
            </AuthorizationGuard>
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
