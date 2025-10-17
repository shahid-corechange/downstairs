import { useConst } from "@chakra-ui/react";
import dayjs from "dayjs";
import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";

import DataTable from "@/components/DataTable";
import { DateRange } from "@/components/DateRangePicker";
import Modal from "@/components/Modal";

import { DATETIME_FORMAT } from "@/constants/datetime";

import { useGetAllSchedules } from "@/services/schedule";

import { PagePagination } from "@/types/pagination";
import Schedule from "@/types/schedule";

import { toDayjs } from "@/utils/datetime";
import { RequestQueryStringOptions } from "@/utils/request";

import getColumns from "./column";

interface DetailModalProps {
  isOpen: boolean;
  onClose: () => void;
}

const DetailModal = ({ isOpen, onClose }: DetailModalProps) => {
  const { t } = useTranslation();
  const columns = useConst(getColumns(t));
  const [requestOptions, setRequestOptions] = useState<
    Partial<RequestQueryStringOptions<Schedule>>
  >({ sort: { canceledAt: "desc" } });

  const defaultDate = [
    toDayjs().startOf("month").toISOString(),
    toDayjs().endOf("month").toISOString(),
  ];

  const [selectedDate, setSelectedDate] = useState(defaultDate);

  const schedules = useGetAllSchedules({
    request: {
      ...requestOptions,
      include: ["customer", "team"],
      only: [
        "id",
        "canceledBy",
        "canceledType",
        "customer.name",
        "team.name",
        "startAt",
        "endAt",
        "canceledAt",
      ],
      filter: {
        ...requestOptions.filter,
        between: {
          canceledAt: [selectedDate[0], selectedDate[1]],
          ...requestOptions.filter?.between,
        },
        eq: {
          status: "cancel",
          ...requestOptions.filter?.eq,
        },
      },
      pagination: "page",
    },
  });

  const handleFilterDate = (dates: DateRange) => {
    const startDate = toDayjs(
      dayjs(dates[0]).startOf("day").format(DATETIME_FORMAT),
      false,
    ).toISOString();
    const endDate = toDayjs(
      dayjs(dates[1]).endOf("day").format(DATETIME_FORMAT),
      false,
    ).toISOString();

    setSelectedDate([startDate, endDate]);
  };

  useEffect(() => {
    if (isOpen) {
      setSelectedDate(defaultDate);
    }
  }, [isOpen]);

  return (
    <Modal
      size="6xl"
      title={t("canceled bookings")}
      bodyContainer={{ p: 8 }}
      isOpen={isOpen}
      onClose={onClose}
    >
      <DataTable
        data={schedules.data?.data || []}
        columns={columns}
        fetchFn={(options) => setRequestOptions(options)}
        isFetching={schedules.isFetching}
        pagination={schedules.data?.pagination as PagePagination}
        sort={requestOptions.sort as Record<string, "asc" | "desc">}
        withDateRange
        dateDefaultFilter={[
          dayjs().startOf("month").toDate(),
          dayjs().endOf("month").toDate(),
        ]}
        onChangeDate={(dates) => {
          handleFilterDate(dates);
        }}
        serverSide
      />
    </Modal>
  );
};

export default DetailModal;
