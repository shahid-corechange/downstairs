import {
  Card,
  CardBody,
  CardHeader,
  Heading,
  useConst,
} from "@chakra-ui/react";
import { useMemo } from "react";
import { useTranslation } from "react-i18next";

import DataTable from "@/components/DataTable";

import { LaundryOrderHistory } from "@/types/laundryOrderHistory";

import getColumns from "./column";

interface HistoryProps {
  laundryOrderHistory?: LaundryOrderHistory[];
}
const History = ({ laundryOrderHistory }: HistoryProps) => {
  const { t } = useTranslation();

  const columns = useConst(getColumns(t));

  const sortedHistory = useMemo(() => {
    if (!laundryOrderHistory) return [];

    return [...laundryOrderHistory].sort((a, b) => {
      const dateA = new Date(a.createdAt);
      const dateB = new Date(b.createdAt);
      return dateB.getTime() - dateA.getTime(); // Descending order (newest first)
    });
  }, [laundryOrderHistory]);

  return (
    <Card>
      <CardHeader>
        <Heading size="sm">{t("order history")}</Heading>
      </CardHeader>
      <CardBody fontSize="sm" pt={0} px={6} pb={6}>
        <DataTable
          columns={columns}
          data={sortedHistory}
          serverSide={false}
          paginatable={false}
          filterable={false}
          searchable={false}
        />
      </CardBody>
    </Card>
  );
};

export default History;
