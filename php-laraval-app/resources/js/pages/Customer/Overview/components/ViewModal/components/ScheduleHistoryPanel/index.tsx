import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import DataTable from "@/components/DataTable";

import { useGetAllSchedules } from "@/services/schedule";

import { PagePagination } from "@/types/pagination";
import Schedule from "@/types/schedule";

import { RequestQueryStringOptions } from "@/utils/request";

import getColumns from "./column";

interface ScheduleHistoryPanelProps extends TabPanelProps {
  userId: number;
}

const ScheduleHistoryPanel = ({
  userId,
  ...props
}: ScheduleHistoryPanelProps) => {
  const { t } = useTranslation();

  const columns = useConst(getColumns(t));
  const [requestOptions, setRequestOptions] = useState<
    Partial<RequestQueryStringOptions<Schedule>>
  >({ sort: { startAt: "desc" } });

  const schedules = useGetAllSchedules({
    request: {
      ...requestOptions,
      include: ["property.address", "team.name"],
      only: [
        "id",
        "startAt",
        "endAt",
        "status",
        "property.address.fullAddress",
        "team.name",
        "canceledType",
      ],
      filter: {
        ...requestOptions.filter,
        eq: {
          userId: userId,
          ...requestOptions.filter?.eq,
        },
        notIn: {
          status: ["booked", "progress"],
          ...requestOptions.filter?.notIn,
        },
      },
      pagination: "page",
    },
  });

  return (
    <TabPanel {...props}>
      <DataTable
        size="md"
        data={schedules.data?.data || []}
        columns={columns}
        fetchFn={(options) => setRequestOptions(options)}
        isFetching={schedules.isFetching}
        pagination={schedules.data?.pagination as PagePagination}
        sort={requestOptions.sort as Record<string, "asc" | "desc">}
        serverSide
      />
    </TabPanel>
  );
};

export default ScheduleHistoryPanel;
