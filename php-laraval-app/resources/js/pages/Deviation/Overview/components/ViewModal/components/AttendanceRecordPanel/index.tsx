import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { useTranslation } from "react-i18next";
import { FiEdit3 } from "react-icons/fi";

import DataTable from "@/components/DataTable";
import { ModalExpansion } from "@/components/Modal/types";

import ScheduleDeviation from "@/types/scheduleDeviation";
import ScheduleEmployee from "@/types/scheduleEmployee";

import { hasPermission } from "@/utils/authorization";

import getColumns from "./column";
import EditAttendanceForm from "./components/AttendanceForm";

interface AttendanceRecordPanelProps extends TabPanelProps {
  deviation: ScheduleDeviation;
  data: ScheduleEmployee[];
  onModalExpansion: (expansion: ModalExpansion) => void;
  onModalShrink: () => void;
  onRefetch: () => void;
}

const AttendanceRecordPanel = ({
  deviation,
  data,
  onModalExpansion,
  onModalShrink,
  onRefetch,
  ...props
}: AttendanceRecordPanelProps) => {
  const { t } = useTranslation();
  const columns = useConst(getColumns(t));

  return (
    <TabPanel {...props}>
      <DataTable
        data={data}
        columns={columns}
        size="md"
        filterable={false}
        actions={[
          {
            label: t("edit attendance"),
            icon: FiEdit3,
            isHidden:
              deviation.isHandled ||
              !hasPermission("schedule workers update attendance"),
            onClick: (row) =>
              onModalExpansion({
                title: t("edit attendance"),
                content: (
                  <EditAttendanceForm
                    schedule={deviation?.schedule}
                    scheduleEmployee={row.original}
                    onCancel={onModalShrink}
                    onRefetch={onRefetch}
                  />
                ),
              }),
          },
        ]}
      />
    </TabPanel>
  );
};

export default AttendanceRecordPanel;
