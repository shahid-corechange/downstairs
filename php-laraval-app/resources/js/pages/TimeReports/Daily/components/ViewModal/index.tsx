import { Tab, TabList, TabPanels, Tabs } from "@chakra-ui/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import AuthorizationGuard from "@/components/AuthorizationGuard";
import Modal from "@/components/Modal";
import { ModalExpansion } from "@/components/Modal/types";

import CashierAttendancePanel from "./components/CashierAttendancePanel";
import DeviationPanel from "./components/DeviationPanel";
import SchedulePanel from "./components/SchedulePanel";
import TimeAdjustmentPanel from "./components/TimeAdjustmentPanel";

interface ViewModalProps {
  workHourId?: number;
  userId?: number;
  date?: string;
  isOpen: boolean;
  onClose: () => void;
}

const ViewModal = ({
  workHourId,
  userId,
  date,
  isOpen,
  onClose,
}: ViewModalProps) => {
  const { t } = useTranslation();
  const [activeTabIndex, setActiveTabIndex] = useState(0);
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
      size="6xl"
      bodyContainer={{ p: 8 }}
      expandableContent={expandableContent}
      expandableTitle={expandableTitle}
      expandableSize="lg"
      isExpanded={isExpanded}
      isOpen={isOpen}
      onClose={handleClose}
      onShrink={handleShrink}
    >
      <Tabs index={activeTabIndex} onChange={handleChangeTab}>
        <TabList>
          <AuthorizationGuard permissions="time reports index">
            <Tab>{t("schedules")}</Tab>
          </AuthorizationGuard>
          <AuthorizationGuard permissions="time reports index">
            <Tab>{t("employees deviations")}</Tab>
          </AuthorizationGuard>
          <AuthorizationGuard permissions="time reports index">
            <Tab>{t("time adjustment")}</Tab>
          </AuthorizationGuard>
          <AuthorizationGuard permissions="time reports index">
            <Tab>{t("cashier attendance")}</Tab>
          </AuthorizationGuard>
        </TabList>
        <TabPanels>
          <AuthorizationGuard permissions="time reports index">
            <SchedulePanel userId={userId} date={date} py={8} />
          </AuthorizationGuard>
          <AuthorizationGuard permissions="time reports index">
            <DeviationPanel userId={userId} date={date} py={8} />
          </AuthorizationGuard>
          <AuthorizationGuard permissions="time reports index">
            <TimeAdjustmentPanel
              userId={userId}
              workHourId={workHourId}
              onModalExpansion={handleModalExpansion}
              onModalShrink={handleShrink}
              py={8}
            />
          </AuthorizationGuard>
          <AuthorizationGuard permissions="time reports index">
            <CashierAttendancePanel userId={userId} date={date} py={8} />
          </AuthorizationGuard>
        </TabPanels>
      </Tabs>
    </Modal>
  );
};

export default ViewModal;
