import { Tab, TabList, TabPanels, Tabs } from "@chakra-ui/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import AuthorizationGuard from "@/components/AuthorizationGuard";
import Modal from "@/components/Modal";
import { ModalExpansion } from "@/components/Modal/types";

import Service from "@/types/service";

import TaskPanel from "./components/TaskPanel";

interface ViewModalProps {
  isOpen: boolean;
  onClose: () => void;
  data?: Service;
}

const ViewModal = ({ data, isOpen, onClose }: ViewModalProps) => {
  const { t } = useTranslation();
  const [isExpanded, setIsExpanded] = useState(false);
  const [expandableContent, setExpandableContent] = useState<React.ReactNode>();
  const [expandableTitle, setExpandableTitle] = useState<string>();
  const [activeTabIndex, setActiveTabIndex] = useState(0);

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
      <Tabs index={activeTabIndex} onChange={handleChangeTab}>
        <TabList>
          <AuthorizationGuard permissions="service tasks index">
            <Tab>{t("tasks")}</Tab>
          </AuthorizationGuard>
        </TabList>
        <TabPanels>
          <AuthorizationGuard permissions="service tasks index">
            <TaskPanel
              service={data}
              onModalExpansion={handleModalExpansion}
              onModalShrink={handleShrink}
              py={8}
            />
          </AuthorizationGuard>
        </TabPanels>
      </Tabs>
    </Modal>
  );
};

export default ViewModal;
