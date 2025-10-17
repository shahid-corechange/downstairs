import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { useTranslation } from "react-i18next";
import { RiExternalLinkLine } from "react-icons/ri";

import DataTable from "@/components/DataTable";

import { useGetEmployeeDeviations } from "@/services/deviation";

import { hasAnyPermissions } from "@/utils/authorization";
import { toDayjs } from "@/utils/datetime";

import getColumns from "./column";

interface DeviationPanelProps extends TabPanelProps {
  userId?: number;
  date?: string;
}

const DeviationPanel = ({ userId, date, ...props }: DeviationPanelProps) => {
  const { t } = useTranslation();

  const columns = useConst(getColumns(t));

  const startDate = toDayjs(date).startOf("day").utc();
  const endDate = toDayjs(date).endOf("day").utc();

  const deviations = useGetEmployeeDeviations({
    request: {
      filter: {
        between: {
          "schedule.startAt": [
            startDate.subtract(1, "day").toISOString(),
            endDate.toISOString(),
          ],
          "schedule.endAt": [
            startDate.toISOString(),
            endDate.add(1, "day").toISOString(),
          ],
        },
        eq: {
          userId: userId,
        },
      },
      include: ["schedule.property.address"],
      only: [
        "id",
        "schedule.startAt",
        "schedule.endAt",
        "schedule.property.address.fullAddress",
        "type",
        "reason",
        "isHandled",
      ],
    },
    query: {
      enabled: !!(userId && date && startDate && endDate),
    },
  });

  return (
    <TabPanel {...props}>
      <DataTable
        data={deviations.data ?? []}
        columns={columns}
        actions={[
          {
            label: t("deviation"),
            icon: RiExternalLinkLine,
            isHidden: !hasAnyPermissions(["deviations index"]),
            onClick: (row) => {
              const id = row.original.id;
              window.open(`/deviations/employee?id.eq=${id}`, "_blank");
            },
          },
        ]}
      />
    </TabPanel>
  );
};

export default DeviationPanel;
