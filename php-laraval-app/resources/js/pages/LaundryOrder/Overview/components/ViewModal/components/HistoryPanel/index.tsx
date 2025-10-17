import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { useTranslation } from "react-i18next";

import DataTable from "@/components/DataTable";

import { LaundryOrder } from "@/types/laundryOrder";

import getColumns from "./column";

interface HistoryPanelProps extends Omit<TabPanelProps, "laundryOrder"> {
  laundryOrder: LaundryOrder;
}

const HistoryPanel = ({ laundryOrder, ...props }: HistoryPanelProps) => {
  const { t } = useTranslation();

  const columns = useConst(getColumns(t));

  return (
    <TabPanel {...props}>
      <DataTable
        data={laundryOrder.histories ?? []}
        columns={columns}
        title={t("history")}
        size="md"
        searchable={false}
        filterable={false}
        paginatable={false}
      />
    </TabPanel>
  );
};

export default HistoryPanel;
