import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { useMemo } from "react";
import { useTranslation } from "react-i18next";

import DataTable from "@/components/DataTable";
import { ModalExpansion } from "@/components/Modal/types";

import { usePageModal } from "@/hooks/modal";

import { useGetTimeAdjustments } from "@/services/timeReports";

import TimeAdjustment from "@/types/timeAdjustment";

import { hasPermission } from "@/utils/authorization";

import getColumns from "./column";
import AddForm from "./components/AddForm";
import DeleteModal from "./components/DeleteModal";
import EditForm from "./components/EditForm";

interface TimeAdjustmentPanelProps extends TabPanelProps {
  userId?: number;
  workHourId?: number;
  onModalExpansion: (expansion: ModalExpansion) => void;
  onModalShrink: () => void;
}

const TimeAdjustmentPanel = ({
  userId,
  workHourId,
  onModalExpansion,
  onModalShrink,
  ...props
}: TimeAdjustmentPanelProps) => {
  const { t } = useTranslation();
  const { modal, modalData, openModal, closeModal } =
    usePageModal<TimeAdjustment>();
  const columns = useConst(getColumns(t));

  const timeAdjustments = useGetTimeAdjustments({
    request: {
      include: ["causer", "schedule.schedule.property.address"],
      only: [
        "id",
        "scheduleEmployeeId",
        "schedule.startAt",
        "schedule.endAt",
        "schedule.schedule.property.address.fullAddress",
        "quarters",
        "reason",
        "causer.fullname",
        "createdAt",
        "updatedAt",
      ],
      filter: {
        eq: {
          "status": "done",
          "schedule.workHourId": workHourId,
        },
      },
      pagination: "page",
    },
    query: {
      enabled: !!workHourId,
    },
  });

  const scheduleIds = useMemo(
    () => timeAdjustments.data?.data.map((item) => item.scheduleEmployeeId),
    [timeAdjustments.data],
  );

  return (
    <TabPanel {...props}>
      <DataTable
        size="md"
        title={t("adjustment")}
        data={timeAdjustments.data?.data ?? []}
        columns={columns}
        withCreate={hasPermission("time adjustments create")}
        withEdit={() => hasPermission("time adjustments update")}
        withDelete={() => hasPermission("time adjustments delete")}
        onCreate={() =>
          onModalExpansion({
            title: t("create time adjustment"),
            content: (
              <AddForm
                scheduleIds={scheduleIds}
                userId={userId}
                workHourId={workHourId}
                onClose={onModalShrink}
                onRefetch={() => timeAdjustments.refetch()}
              />
            ),
          })
        }
        onEdit={(row) =>
          onModalExpansion({
            title: t("edit time adjustment"),
            content: (
              <EditForm
                timeAdjustment={row.original}
                onClose={onModalShrink}
                onRefetch={() => timeAdjustments.refetch()}
              />
            ),
          })
        }
        onDelete={(row) => openModal("delete", row.original)}
      />
      <DeleteModal
        timeAdjustmentId={modalData?.id}
        isOpen={modal === "delete"}
        onClose={closeModal}
        onRefetch={() => timeAdjustments.refetch()}
      />
    </TabPanel>
  );
};

export default TimeAdjustmentPanel;
