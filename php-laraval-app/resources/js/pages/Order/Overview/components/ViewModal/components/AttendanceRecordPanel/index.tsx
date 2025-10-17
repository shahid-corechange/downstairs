import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { useTranslation } from "react-i18next";

import DataTable from "@/components/DataTable";

import ScheduleEmployee from "@/types/scheduleEmployee";

import getColumns from "./column";

interface AttendanceRecordPanelProps extends TabPanelProps {
  data: ScheduleEmployee[];
}

const AttendanceRecordPanel = ({
  data,
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
        searchable={false}
        filterable={false}
        paginatable={false}
      />
    </TabPanel>
  );
};

export default AttendanceRecordPanel;
