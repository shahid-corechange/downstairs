import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";
import { RiExternalLinkLine } from "react-icons/ri";
import { TbCalendarCancel } from "react-icons/tb";

import DataTable from "@/components/DataTable";
import ScheduleCancelConfirmation from "@/components/ScheduleCancelConfirmation";

import { DATE_FORMAT } from "@/constants/datetime";

import { usePageModal } from "@/hooks/modal";

import { ViewModalPageProps } from "@/pages/Company/Overview/types";

import { useGetAllSchedules } from "@/services/schedule";

import Customer from "@/types/customer";
import { PagePagination } from "@/types/pagination";
import Schedule from "@/types/schedule";

import { toDayjs } from "@/utils/datetime";
import { RequestQueryStringOptions } from "@/utils/request";

import { PageProps } from "@/types";

import getColumns from "./column";

interface SchedulePanelProps extends TabPanelProps {
  customers: Customer[];
  onRefetch: () => void;
}

const SchedulePanel = ({
  customers,
  onRefetch,
  ...props
}: SchedulePanelProps) => {
  const { t } = useTranslation();

  const { creditRefundTimeWindow } =
    usePage<PageProps<ViewModalPageProps>>().props;

  const { modal, modalData, openModal, closeModal } = usePageModal<
    Schedule,
    "cancel"
  >();

  const columns = useConst(getColumns(t));
  const [requestOptions, setRequestOptions] = useState<
    Partial<RequestQueryStringOptions<Schedule>>
  >({ sort: { startAt: "asc" } });

  const schedules = useGetAllSchedules({
    request: {
      ...requestOptions,
      include: ["property.address", "team.name", "refund", "detail"],
      only: [
        "id",
        "teamId",
        "startAt",
        "endAt",
        "status",
        "property.address.fullAddress",
        "team.name",
        "refund.amount",
        "detail.laundryType",
        "detail.laundryOrderId",
      ],
      filter: {
        ...requestOptions.filter,
        in: {
          customerId: customers.map((customer) => customer.id),
          ...requestOptions.filter?.in,
        },
        notIn: {
          status: ["done", "cancel"],
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
        actions={[
          {
            label: t("view"),
            icon: RiExternalLinkLine,
            onClick: (row) => {
              const id = row.original.id;
              const startAt = toDayjs(row.original.startAt);
              const startOfWeek = startAt.weekday(0).format(DATE_FORMAT);
              const endOfWeek = startAt.weekday(6).format(DATE_FORMAT);
              const shownTeamIds = row.original.teamId;

              window.open(
                `/schedules?scheduleId=${id}&startAt.gte=${startOfWeek}&endAt.lte=${endOfWeek}&view.shownTeamIds=${shownTeamIds}&view.showWeekend=true&view.showEarlyHours=true&view.showLateHours=true`,
                "_blank",
              );
            },
          },
          {
            label: t("cancel this schedule"),
            icon: TbCalendarCancel,
            colorScheme: "red",
            color: "red.500",
            _dark: { color: "red.200" },
            onClick: (row) => {
              openModal("cancel", row.original);
            },
          },
        ]}
        serverSide
      />

      {modalData && (
        <ScheduleCancelConfirmation
          schedule={modalData}
          creditRefundTimeWindow={creditRefundTimeWindow}
          isOpen={modal === "cancel"}
          onClose={closeModal}
          onSuccess={onRefetch}
        />
      )}
    </TabPanel>
  );
};

export default SchedulePanel;
