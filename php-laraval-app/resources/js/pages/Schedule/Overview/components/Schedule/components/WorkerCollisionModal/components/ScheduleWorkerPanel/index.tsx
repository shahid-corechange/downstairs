import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { Dayjs } from "dayjs";
import { useTranslation } from "react-i18next";
import { AiOutlineSwap } from "react-icons/ai";

import DataTable from "@/components/DataTable";
import { ModalExpansion } from "@/components/Modal/types";

import ScheduleEmployee from "@/types/scheduleEmployee";

import { ChangedWorkers } from "../../types";
import ChangeForm from "../ChangeForm";
import getColumns from "./column";

interface ScheduleWorkerPanelProps extends TabPanelProps {
  scheduleWorkerIds: number[];
  changedWorkers: ChangedWorkers[];
  collidedWorkers: ScheduleEmployee[];
  startAt: string | Dayjs;
  endAt: string | Dayjs;
  onChangeWorker: (worker: ScheduleEmployee, userId: number) => void;
  onModalExpansion: (expansion: ModalExpansion) => void;
  onModalShrink: () => void;
}

const ScheduleWorkerPanel = ({
  scheduleWorkerIds,
  changedWorkers,
  collidedWorkers,
  startAt,
  endAt,
  onChangeWorker,
  onModalExpansion,
  onModalShrink,
  ...props
}: ScheduleWorkerPanelProps) => {
  const { t } = useTranslation();

  const columns = useConst(getColumns(t));

  return (
    <TabPanel {...props}>
      <DataTable
        data={collidedWorkers}
        columns={columns}
        size="md"
        searchable={false}
        filterable={false}
        paginatable={false}
        actions={[
          {
            label: t("change"),
            icon: AiOutlineSwap,
            onClick: (row) => {
              const extraExcludedWorkerIds = changedWorkers.map(
                (item) => item.userId,
              );
              const excludedWorkerIds = [
                ...scheduleWorkerIds,
                ...extraExcludedWorkerIds,
              ];

              onModalExpansion({
                title: t("change worker"),
                content: (
                  <ChangeForm
                    previousWorkerName={row.original.user?.fullname ?? ""}
                    excludedWorkerIds={excludedWorkerIds}
                    startAt={startAt}
                    endAt={endAt}
                    onSubmit={(userId) => onChangeWorker(row.original, userId)}
                    onClose={onModalShrink}
                  />
                ),
              });
            },
          },
        ]}
      />
    </TabPanel>
  );
};

export default ScheduleWorkerPanel;
