import { Flex, Spinner, Tab, TabList, TabPanels, Tabs } from "@chakra-ui/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import AuthorizationGuard from "@/components/AuthorizationGuard";
import Modal from "@/components/Modal";
import { ModalExpansion } from "@/components/Modal/types";

import { useGetSchedule } from "@/services/schedule";

import InfoPanel from "./components/InfoPanel";
import TaskPanel from "./components/TaskPanel";
import WorkerPanel from "./components/WorkerPanel";

interface ScheduleModalProps {
  scheduleId: number;
  isOpen: boolean;
  onClose: () => void;
}

const ScheduleModal = ({ scheduleId, isOpen, onClose }: ScheduleModalProps) => {
  const { t } = useTranslation();

  const schedule = useGetSchedule(scheduleId, {
    request: {
      include: [
        "notes",
        "allEmployees.user",
        "allEmployees.schedule.team.users",
        "subscription.tasks",
        "service.tasks",
        "user",
        "property.address.city",
        "property.keyInformation",
        "refund",
        "customer",
        "team",
        "items.item.tasks",
        "items.item.services",
        "tasks",
        "detail",
      ],
      only: [
        "id",
        "customerId",
        "teamId",
        "serviceId",
        "userId",
        "startAt",
        "endAt",
        "quarters",
        "keyInformation",
        "notes.propertyNote",
        "notes.subscriptionNote",
        "notes.note",
        "note",
        "isFixed",
        "hasDeviation",
        "workStatus",
        "status",
        "allEmployees.userId",
        "allEmployees.status",
        "allEmployees.deletedAt",
        "allEmployees.user.fullname",
        "allEmployees.schedule.team.users.id",
        "subscription.fixedPriceId",
        "subscription.tasks.id",
        "subscription.tasks.name",
        "subscription.tasks.description",
        "service.name",
        "service.tasks.id",
        "service.tasks.name",
        "service.tasks.description",
        "user.id",
        "user.fullname",
        "user.totalCredits",
        "property.address.city.name",
        "property.address.address",
        "property.address.fullAddress",
        "property.address.latitude",
        "property.address.longitude",
        "property.keyInformation.keyPlace",
        "refund.amount",
        "customer.membershipType",
        "team.id",
        "team.name",
        "team.color",
        "team.totalWorkers",
        "items.paymentMethod",
        "items.item.id",
        "items.itemableType",
        "items.itemableId",
        "items.item.name",
        "items.item.deletedAt",
        "items.item.tasks.id",
        "items.item.tasks.name",
        "items.item.tasks.description",
        "items.item.creditPrice",
        "items.item.services.id",
        "tasks.id",
        "tasks.name",
        "tasks.description",
        "tasks.translations",
        "detail.laundryOrderId",
        "detail.laundryType",
      ],
    },
  });

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
      expandableSize={isExpanded && activeTabIndex === 0 ? "4xl" : "md"}
      isOpen={isOpen}
      isExpanded={isExpanded}
      onClose={handleClose}
      onShrink={handleShrink}
    >
      {!schedule.isFetching && schedule.data ? (
        <Tabs index={activeTabIndex} onChange={handleChangeTab}>
          <TabList>
            <AuthorizationGuard permissions="schedules read">
              <Tab>{t("information")}</Tab>
            </AuthorizationGuard>
            <AuthorizationGuard permissions="schedule workers index">
              <Tab>{t("workers")}</Tab>
            </AuthorizationGuard>
            <AuthorizationGuard permissions="schedule tasks index">
              <Tab>{t("tasks")}</Tab>
            </AuthorizationGuard>
          </TabList>

          <TabPanels>
            <AuthorizationGuard permissions="schedules read">
              <InfoPanel
                schedule={schedule.data}
                scheduleQueryKey={schedule.queryKey}
                onModalClose={handleClose}
                onModalExpansion={handleModalExpansion}
                onModalShrink={handleShrink}
                py={8}
              />
            </AuthorizationGuard>
            <AuthorizationGuard permissions="schedule workers index">
              <WorkerPanel
                schedule={schedule.data}
                scheduleQueryKey={schedule.queryKey}
                py={8}
              />
            </AuthorizationGuard>
            <AuthorizationGuard permissions="schedule tasks index">
              <TaskPanel
                schedule={schedule.data}
                scheduleQueryKey={schedule.queryKey}
                onModalExpansion={handleModalExpansion}
                onModalShrink={handleShrink}
                py={8}
              />
            </AuthorizationGuard>
          </TabPanels>
        </Tabs>
      ) : (
        <Flex h="xl" alignItems="center" justifyContent="center">
          <Spinner size="xl" />
        </Flex>
      )}
    </Modal>
  );
};

export default ScheduleModal;
