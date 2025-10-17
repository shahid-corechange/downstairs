import { Tab, TabList, TabPanels, Tabs } from "@chakra-ui/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import AuthorizationGuard from "@/components/AuthorizationGuard";
import Modal from "@/components/Modal";
import { ModalExpansion } from "@/components/Modal/types";

import Addon from "@/types/addon";

import TaskPanel from "./components/TaskPanel";

interface ViewModalProps {
  isOpen: boolean;
  onClose: () => void;
  data?: Addon;
}

const ViewModal = ({ data, onClose, isOpen }: ViewModalProps) => {
  const { t } = useTranslation();

  const [isExpanded, setIsExpanded] = useState(false);
  const [expandableContent, setExpandableContent] = useState<React.ReactNode>();
  const [expandableTitle, setExpandableTitle] = useState<string>();

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

  const handleClose = () => {
    handleShrink();
    onClose();
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
      <Tabs onChange={handleShrink}>
        <TabList>
          <AuthorizationGuard permissions="addon tasks index">
            <Tab>{t("tasks")}</Tab>
          </AuthorizationGuard>
        </TabList>
        <TabPanels>
          <AuthorizationGuard permissions="addon tasks index">
            <TaskPanel
              addOn={data}
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
