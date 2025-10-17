import { Badge, TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { useTranslation } from "react-i18next";

import DataTable from "@/components/DataTable";

import Order from "@/types/order";

import getColumns from "./column";

interface RowPanelProps extends Omit<TabPanelProps, "order"> {
  order: Order;
}

const FixedPricePanel = ({ order, ...props }: RowPanelProps) => {
  const { t } = useTranslation();
  const columns = useConst(getColumns(t));

  return (
    <TabPanel {...props}>
      {order?.fixedPrice?.isPerOrder && (
        <Badge colorScheme="green" variant="subtle">
          {t("per booking")}
        </Badge>
      )}
      <br />
      {order?.fixedPrice?.meta?.includeLaundry && (
        <Badge colorScheme="purple" variant="subtle">
          {t("include laundry")}
        </Badge>
      )}

      <DataTable
        data={order?.fixedPrice?.rows ?? []}
        columns={columns}
        title={t("row")}
        size="md"
        searchable={false}
        filterable={false}
        paginatable={false}
      />
    </TabPanel>
  );
};

export default FixedPricePanel;
