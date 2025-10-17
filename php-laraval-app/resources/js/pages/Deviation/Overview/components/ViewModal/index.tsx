import { Flex, Spinner, Tab, TabList, TabPanels, Tabs } from "@chakra-ui/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import Modal from "@/components/Modal";
import { ModalExpansion } from "@/components/Modal/types";

import { useGetDeviation } from "@/services/deviation";
import { useGetAllScheduleWorkers } from "@/services/schedule";

import AttendanceRecordPanel from "./components/AttendanceRecordPanel";
import ConfirmationModal from "./components/ConfirmationModal";
import DetailPanel from "./components/DetailPanel";
import SummaryPanel from "./components/SummaryPanel";
import TaskRecordPanel from "./components/TaskRecordPanel";
import { FormValues } from "./types";

interface ViewModalProps {
  isOpen: boolean;
  onClose: () => void;
  deviationId?: number;
}

const ViewModal = ({ deviationId, isOpen, onClose }: ViewModalProps) => {
  const { t } = useTranslation();
  const [isExpanded, setIsExpanded] = useState(false);
  const [expandableContent, setExpandableContent] = useState<React.ReactNode>();
  const [expandableTitle, setExpandableTitle] = useState<string>();
  const [formValues, setFormValues] = useState<FormValues>();
  const [activeTabIndex, setActiveTabIndex] = useState(0);

  const deviation = useGetDeviation(deviationId, {
    request: {
      include: [
        "schedule.subscription.detail",
        "schedule.addonSummaries",
        "schedule.scheduleTasks",
        "meta.items",
      ],
      only: [
        "id",
        "types",
        "isHandled",
        "schedule.id",
        "schedule.scheduleableId",
        "schedule.startAt",
        "schedule.endAt",
        "schedule.subscription.detail.quarters",
        "schedule.actualStartAt",
        "schedule.actualEndAt",
        "schedule.actualQuarters",
        "schedule.addonSummaries.id",
        "schedule.addonSummaries.name",
        "schedule.addonSummaries.isCharge",
        "schedule.scheduleTasks.name",
        "schedule.scheduleTasks.description",
        "schedule.scheduleTasks.isCompleted",
        "meta.actualQuarters",
        "meta.items.id",
        "meta.items.isCharge",
      ],
    },
  });

  const scheduleEmployees = useGetAllScheduleWorkers(
    deviation.data?.schedule?.id ?? 0,
    {
      request: {
        include: ["user"],
        only: [
          "id",
          "user.id",
          "user.fullname",
          "startAt",
          "endAt",
          "status",
          "scheduleId",
        ],
        filter: {
          eq: { scheduleId: deviation.data?.schedule?.id },
        },
      },
      query: {
        enabled: !!deviation.data?.schedule?.id,
      },
    },
  );

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
    setFormValues(undefined);
    handleShrink();
    onClose();
    setActiveTabIndex(0);
  };

  const handleRefetchAttendance = () => {
    scheduleEmployees.refetch();
    deviation.refetch();
  };

  return (
    <>
      <Modal
        bodyContainer={{ p: 8 }}
        expandableContent={expandableContent}
        expandableTitle={expandableTitle}
        expandableSize="lg"
        isExpanded={isExpanded}
        isOpen={isOpen}
        onClose={handleClose}
        onShrink={handleShrink}
      >
        {!deviation.isFetching && deviation.data ? (
          <Tabs index={activeTabIndex} onChange={handleChangeTab}>
            <TabList>
              <Tab>{t("summary")}</Tab>
              <Tab>{t("detail")}</Tab>
              <Tab>{t("attendance record")}</Tab>
              {deviation.data.types.includes("incomplete task") && (
                <Tab>{t("task record")}</Tab>
              )}
            </TabList>
            <TabPanels>
              <SummaryPanel
                data={deviation.data}
                py={8}
                onHandle={setFormValues}
              />
              <DetailPanel data={deviation.data} py={8} />
              <AttendanceRecordPanel
                deviation={deviation.data}
                data={scheduleEmployees.data ?? []}
                onModalExpansion={handleModalExpansion}
                onModalShrink={handleShrink}
                onRefetch={handleRefetchAttendance}
                py={8}
              />
              {deviation.data.types.includes("incomplete task") && (
                <TaskRecordPanel data={deviation.data} py={8} />
              )}
            </TabPanels>
          </Tabs>
        ) : (
          <Flex h="xs" alignItems="center" justifyContent="center">
            <Spinner size="md" />
          </Flex>
        )}
      </Modal>
      <ConfirmationModal
        scheduleEmployees={scheduleEmployees.data ?? []}
        formValues={formValues}
        deviation={deviation.data}
        onClose={() => setFormValues(undefined)}
        onSuccess={handleClose}
      />
    </>
  );
};

export default ViewModal;
