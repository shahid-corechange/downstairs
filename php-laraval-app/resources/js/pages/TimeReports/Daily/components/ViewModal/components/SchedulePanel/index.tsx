import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { useTranslation } from "react-i18next";
import { RiExternalLinkLine } from "react-icons/ri";

import DataTable from "@/components/DataTable";

import { useGetAllSchedules } from "@/services/schedule";

import { hasAnyPermissions } from "@/utils/authorization";
import { toDayjs } from "@/utils/datetime";

import getColumns from "./column";

interface SchedulePanelProps extends TabPanelProps {
  userId?: number;
  date?: string;
}

const SchedulePanel = ({ userId, date, ...props }: SchedulePanelProps) => {
  const { t } = useTranslation();
  const columns = useConst(getColumns(t));
  const startDate = toDayjs(date).startOf("day").utc();
  const endDate = toDayjs(date).endOf("day").utc();

  const schedules = useGetAllSchedules({
    request: {
      include: ["property.address", "customer"],
      only: [
        "id",
        "startAt",
        "endAt",
        "customer.name",
        "property.address.fullAddress",
        "hasDeviation",
      ],
      filter: {
        between: {
          startAt: [
            startDate.subtract(1, "day").toISOString(),
            endDate.toISOString(),
          ],
          endAt: [startDate.toISOString(), endDate.add(1, "day").toISOString()],
        },
        eq: {
          "status": "done",
          "activeEmployees.userId": userId,
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
      <DataTable
        size="md"
        data={schedules.data?.data ?? []}
        columns={columns}
        actions={[
          {
            label: t("deviation"),
            icon: RiExternalLinkLine,
            isHidden: (row) =>
              !hasAnyPermissions(["deviations index"]) ||
              !row.original.hasDeviation,
            onClick: (row) => {
              const id = row.original.id;
              window.open(`/deviations?scheduleCleaningId.eq=${id}`, "_blank");
            },
          },
        ]}
      />
    </TabPanel>
  );
};

export default SchedulePanel;
