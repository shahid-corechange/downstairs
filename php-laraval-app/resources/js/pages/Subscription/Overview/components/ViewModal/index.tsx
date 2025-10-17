import { Flex, Spinner, Tab, TabList, TabPanels, Tabs } from "@chakra-ui/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import AuthorizationGuard from "@/components/AuthorizationGuard";
import Modal from "@/components/Modal";
import { ModalExpansion } from "@/components/Modal/types";

import { useGetSubscription } from "@/services/subscription";

import TaskPanel from "./components/TaskPanel";

interface ViewModalProps {
  isOpen: boolean;
  onClose: () => void;
  subscriptionId?: number;
}

const ViewModal = ({ subscriptionId, isOpen, onClose }: ViewModalProps) => {
  const { t } = useTranslation();
  const [isExpanded, setIsExpanded] = useState(false);
  const [expandableContent, setExpandableContent] = useState<React.ReactNode>();
  const [expandableTitle, setExpandableTitle] = useState<string>();
  const [activeTabIndex, setActiveTabIndex] = useState(0);

  const subscription = useGetSubscription(subscriptionId, {
    request: {
      include: ["tasks"],
      only: [
        "id",
        "tasks.id",
        "tasks.name",
        "tasks.description",
        "tasks.translations",
      ],
    },
    query: {
      enabled: !!subscriptionId && isOpen,
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
      expandableContent={expandableContent}
      expandableTitle={expandableTitle}
      isExpanded={isExpanded}
      isOpen={isOpen}
      onClose={handleClose}
      onShrink={handleShrink}
    >
      {!subscription.isFetching && subscription.data ? (
        <Tabs index={activeTabIndex} onChange={handleChangeTab}>
          <TabList>
            <AuthorizationGuard permissions="subscription tasks index">
              <Tab>{t("tasks")}</Tab>
            </AuthorizationGuard>
          </TabList>
          <TabPanels>
            <AuthorizationGuard permissions="subscription tasks index">
              <TaskPanel
                subscription={subscription.data}
                onModalExpansion={handleModalExpansion}
                onModalShrink={handleShrink}
                onRefetch={() => subscription.refetch()}
                py={8}
              />
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
