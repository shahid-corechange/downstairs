import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { useTranslation } from "react-i18next";

import DataTable from "@/components/DataTable";

import { useGetAllCashierAttendances } from "@/services/cashierAttendance";

import { toDayjs } from "@/utils/datetime";

import getColumns from "./column";

interface CashierAttendancePanelProps extends TabPanelProps {
  userId?: number;
  date?: string;
}

const CashierAttendancePanel = ({
  userId,
  date,
  ...props
}: CashierAttendancePanelProps) => {
  const { t } = useTranslation();
  const columns = useConst(getColumns(t));
  const startDate = toDayjs(date).startOf("day").utc();
  const endDate = toDayjs(date).endOf("day").utc();

  const attendances = useGetAllCashierAttendances({
    request: {
      include: ["store", "checkInCauser", "checkOutCauser"],
      only: [
        "id",
        "userId",
        "store.name",
        "checkInAt",
        "checkOutAt",
        "checkInCauser.fullname",
        "checkOutCauser.fullname",
        "totalHours",
      ],
      filter: {
        between: {
          checkInAt: [
            startDate.subtract(1, "day").toISOString(),
            endDate.toISOString(),
          ],
          checkOutAt: [
            startDate.toISOString(),
            endDate.add(1, "day").toISOString(),
          ],
        },
        eq: {
          userId: userId,
        },
      },
      pagination: "page",
    },
    query: {
      enabled: !!userId && !!date,
    },
  });

  return (
    <TabPanel {...props}>
      <DataTable size="md" data={attendances.data ?? []} columns={columns} />
    </TabPanel>
  );
};

export default CashierAttendancePanel;
