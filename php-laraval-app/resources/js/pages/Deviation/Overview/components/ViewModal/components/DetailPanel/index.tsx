import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { useTranslation } from "react-i18next";
import { AiOutlineCheckCircle, AiOutlineUndo } from "react-icons/ai";

import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import HandleModal from "@/pages/Deviation/Employee/components/HandleModal";
import RevertModal from "@/pages/Deviation/Employee/components/RevertModal";

import { useGetEmployeeDeviationsBySchedule } from "@/services/deviation";

import Deviation from "@/types/deviation";
import ScheduleDeviation from "@/types/scheduleDeviation";

import { hasPermission } from "@/utils/authorization";

import getColumns from "./column";

interface DetailPanelProps extends TabPanelProps {
  data: ScheduleDeviation;
}

const DetailPanel = ({ data, ...props }: DetailPanelProps) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<
    Deviation,
    "handle" | "revert"
  >();

  const columns = useConst(getColumns(t));
  const deviations = useGetEmployeeDeviationsBySchedule(
    data?.schedule?.id ?? 0,
    {
      request: {
        include: ["user", "schedule"],
        only: [
          "id",
          "user.id",
          "user.fullname",
          "schedule.id",
          "schedule.status",
          "schedule.endAt",
          "type",
          "reason",
          "isHandled",
        ],
      },
    },
  );

  const handleClose = () => {
    closeModal();
    deviations.refetch();
  };

  return (
    <TabPanel {...props}>
      <DataTable
        data={deviations.data ?? []}
        columns={columns}
        actions={[
          (row) =>
            !row.original.isHandled
              ? {
                  label: t("handle"),
                  colorScheme: "green",
                  color: "green.500",
                  _dark: {
                    color: "green.200",
                  },
                  icon: AiOutlineCheckCircle,
                  isHidden: !hasPermission("deviations handle"),
                  onClick: (row) => {
                    openModal("handle", row.original);
                  },
                }
              : null,
          (row) =>
            !row.original.isHandled &&
            row.original.type === "canceled" &&
            ["booked", "progress"].includes(row.original.schedule?.status ?? "")
              ? {
                  label: t("revert"),
                  icon: AiOutlineUndo,
                  isHidden: !hasPermission("deviations handle"),
                  onClick: (row) => {
                    openModal("revert", row.original);
                  },
                }
              : null,
        ]}
      />

      <RevertModal
        deviation={modalData!}
        isOpen={!!modalData && modal === "revert"}
        onClose={handleClose}
      />
      <HandleModal
        data={modalData}
        isOpen={modal === "handle"}
        onClose={handleClose}
      />
    </TabPanel>
  );
};

export default DetailPanel;
