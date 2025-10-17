import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { useTranslation } from "react-i18next";

import DataTable from "@/components/DataTable";

import ScheduleDeviation from "@/types/scheduleDeviation";

import getColumns from "./column";

interface TaskRecordPanelProps extends TabPanelProps {
  data: ScheduleDeviation;
}

const TaskRecordPanel = ({ data, ...props }: TaskRecordPanelProps) => {
  const { t } = useTranslation();

  const columns = useConst(getColumns(t));

  return (
    <TabPanel {...props}>
      <DataTable
        data={data?.schedule?.scheduleTasks ?? []}
        columns={columns}
        size="md"
      />
    </TabPanel>
  );
};

export default TaskRecordPanel;
