import { useConst } from "@chakra-ui/react";
import dayjs from "dayjs";
import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";

import DataTable from "@/components/DataTable";
import { DateRange } from "@/components/DateRangePicker";
import Modal from "@/components/Modal";

import { DATETIME_FORMAT, DATE_FORMAT } from "@/constants/datetime";

import { useAddonStatistic } from "@/services/dashboard";

import { toDayjs } from "@/utils/datetime";

import getColumns from "./column";

interface DetailModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export type GroupedDataScheduleCleaningProduct = {
  name: string;
  startAt: string;
  endAt: string;
  creditPrice: number;
  priceWithVat: number;
  total: number;
};

const DetailModal = ({ isOpen, onClose }: DetailModalProps) => {
  const { t } = useTranslation();

  const columns = useConst(getColumns(t));

  const defaultDate = [
    toDayjs().startOf("month").utc().format(DATETIME_FORMAT),
    toDayjs().endOf("month").utc().format(DATETIME_FORMAT),
  ];

  const [selectedDate, setSelectedDate] = useState(defaultDate);

  const addonStatistic = useAddonStatistic(selectedDate[0], selectedDate[1], {
    enabled: isOpen,
  });

  const handleFilterDate = (dates: DateRange) => {
    const startDate = dayjs(dates[0]).format(DATE_FORMAT);
    const endDate = dayjs(dates[1]).format(DATE_FORMAT);

    setSelectedDate([
      toDayjs(startDate, false).startOf("day").utc().format(DATETIME_FORMAT),
      toDayjs(endDate, false).endOf("day").utc().format(DATETIME_FORMAT),
    ]);
  };

  useEffect(() => {
    if (isOpen) {
      setSelectedDate(defaultDate);
    }
  }, [isOpen]);

  return (
    <Modal
      size="3xl"
      title={t("add on")}
      bodyContainer={{ p: 8 }}
      isOpen={isOpen}
      onClose={onClose}
    >
      <DataTable
        data={addonStatistic.data || []}
        minDate={toDayjs().startOf("year").toDate()}
        maxDate={toDayjs().endOf("year").toDate()}
        columns={columns}
        searchable={false}
        isLoading={addonStatistic.isFetching}
        withDateRange
        dateDefaultFilter={[
          new Date(toDayjs().startOf("month").format(DATE_FORMAT)),
          new Date(toDayjs().endOf("month").format(DATE_FORMAT)),
        ]}
        onChangeDate={(dates) => {
          handleFilterDate(dates);
        }}
      />
    </Modal>
  );
};

export default DetailModal;
