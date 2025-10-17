import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import DataTable from "@/components/DataTable";

import { useGetAllSchedules } from "@/services/schedule";

import Customer from "@/types/customer";
import { PagePagination } from "@/types/pagination";
import Schedule from "@/types/schedule";

import { RequestQueryStringOptions } from "@/utils/request";

import getColumns from "./column";

interface ScheduleHistoryPanelProps extends TabPanelProps {
  customers: Customer[];
}

const ScheduleHistoryPanel = ({
  customers,
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
        in: {
          customerId: customers.map((customer) => customer.id),
          ...requestOptions.filter?.in,
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
