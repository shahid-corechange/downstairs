import { useConst } from "@chakra-ui/react";
import { useTranslation } from "react-i18next";
import { RiExternalLinkLine } from "react-icons/ri";

import DataTable from "@/components/DataTable";
import Modal from "@/components/Modal";

import { DATE_FORMAT } from "@/constants/datetime";

import Schedule from "@/types/schedule";

import { toDayjs } from "@/utils/datetime";

import getColumns from "./column";

export interface ScheduleCollisionModalProps {
  isOpen: boolean;
  onClose: () => void;
  data: Schedule[];
}

const ScheduleCollisionModal = ({
  data,
  onClose,
  isOpen,
}: ScheduleCollisionModalProps) => {
  const { t } = useTranslation();
  const columns = useConst(getColumns(t));

  return (
    <Modal title={t("collided schedules")} onClose={onClose} isOpen={isOpen}>
      <DataTable
        data={data}
        columns={columns}
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
        ]}
      />
    </Modal>
  );
};

export default ScheduleCollisionModal;
