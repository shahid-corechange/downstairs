import {
  Button,
  Flex,
  TabPanel,
  TabPanelProps,
  useConst,
} from "@chakra-ui/react";
import { QueryKey } from "@tanstack/react-query";
import { useMemo, useState } from "react";
import { useTranslation } from "react-i18next";
import {
  AiOutlineCheckCircle,
  AiOutlineCloseCircle,
  AiOutlineUndo,
} from "react-icons/ai";
import { LuTrash } from "react-icons/lu";

import Alert from "@/components/Alert";
import AuthorizationGuard from "@/components/AuthorizationGuard";
import Autocomplete from "@/components/Autocomplete";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import useScheduleStore from "@/pages/Schedule/Overview/store";

import { queryClient } from "@/services/client";
import {
  useAddScheduleWorkersMutation,
  useGetAvailableWorkers,
} from "@/services/schedule";

import Schedule from "@/types/schedule";
import ScheduleEmployee from "@/types/scheduleEmployee";

import { hasPermission } from "@/utils/authorization";
import { isReadonly } from "@/utils/schedule";

import getColumns from "./column";
import ChangeStatusConfirmation from "./components/ChangeStatusConfirmation";
import RemoveConfirmation from "./components/RemoveConfirmation";
import RevertConfirmation from "./components/RevertConfirmation";

interface WorkerPanelProps extends TabPanelProps {
  schedule: Schedule;
  scheduleQueryKey: QueryKey;
}

const WorkerPanel = ({
  schedule,
  scheduleQueryKey,
  ...props
}: WorkerPanelProps) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<
    ScheduleEmployee,
    "changeStatus" | "remove" | "revert"
  >();

  const addWorkersMutation = useAddScheduleWorkersMutation();
  const updateSchedule = useScheduleStore((state) => state.updateSchedule);

  const [selectedWorkerIds, setSelectedWorkerIds] = useState<number[]>([]);
  const [isLoadingAdd, setIsLoadingAdd] = useState(false);

  const columns = useConst(getColumns(t));

  const availableWorkers = useGetAvailableWorkers(
    schedule.startAt,
    schedule.endAt,
    [],
    {
      request: {
        only: ["id", "fullname"],
      },
    },
  );

  const availableWorkerOptions = useMemo(() => {
    return (availableWorkers.data ?? []).map((worker) => ({
      label: worker.fullname,
      value: worker.id,
    }));
  }, [availableWorkers.data]);

  const handleAddWorkers = () => {
    setIsLoadingAdd(true);

    addWorkersMutation.mutate(
      {
        scheduleId: schedule.id,
        workerIds: selectedWorkerIds,
      },
      {
        onSettled: () => setIsLoadingAdd(false),
        onSuccess: ({ response, data }) => {
          setSelectedWorkerIds([]);
          queryClient.setQueryData(scheduleQueryKey, response);
          updateSchedule(data);
        },
      },
    );
  };

  return (
    <TabPanel {...props}>
      {!isReadonly(schedule) && (
        <AuthorizationGuard permissions="schedule workers create">
          <Alert
            status="warning"
            title={t("warning")}
            message={t("schedule workers alert body")}
            fontSize="small"
            mb={6}
          />
          <Flex align="center" gap={4} mb={2}>
            <Autocomplete
              options={availableWorkerOptions}
              placeholder={t("type worker name")}
              value={JSON.stringify(selectedWorkerIds)}
              onChange={(e) =>
                setSelectedWorkerIds(
                  e.target.value ? JSON.parse(e.target.value) : [],
                )
              }
              isLoading={availableWorkers.isLoading}
              multiple
            />
            <Button
              fontSize="small"
              onClick={handleAddWorkers}
              isLoading={isLoadingAdd}
              loadingText={t("please wait")}
              isDisabled={selectedWorkerIds.length === 0}
            >
              {t("add")}
            </Button>
          </Flex>
        </AuthorizationGuard>
      )}

      <DataTable
        data={schedule.allEmployees ?? []}
        columns={columns}
        size="md"
        searchable={false}
        filterable={false}
        paginatable={false}
        actions={
          !isReadonly(schedule)
            ? [
                (row) => ({
                  label: row.original.deletedAt ? t("enable") : t("disable"),
                  icon: row.original.deletedAt
                    ? AiOutlineCheckCircle
                    : AiOutlineCloseCircle,
                  colorScheme: row.original.deletedAt ? "green" : "red",
                  color: row.original.deletedAt ? "green.500" : "red.500",
                  _dark: {
                    color: row.original.deletedAt ? "green.200" : "red.200",
                  },
                  isHidden: !hasPermission(
                    row.original.deletedAt
                      ? "schedule workers enable"
                      : "schedule workers disable",
                  ),
                  onClick: (row) => openModal("changeStatus", row.original),
                }),
                (row) =>
                  (row.original.schedule?.team?.users ?? []).findIndex(
                    (user) => user.id === row.original.userId,
                  ) > -1
                    ? null
                    : {
                        label: t("remove"),
                        icon: LuTrash,
                        colorScheme: "red",
                        color: "red.500",
                        _dark: { color: "red.200" },
                        isHidden: !hasPermission("schedule workers disable"),
                        onClick: (row) => openModal("remove", row.original),
                      },
                (row) =>
                  row.original.status !== "cancel"
                    ? null
                    : {
                        label: t("revert"),
                        icon: AiOutlineUndo,
                        isHidden: !hasPermission("schedule workers enable"),
                        onClick: () => openModal("revert", row.original),
                      },
              ]
            : [
                (row) =>
                  row.original.status === "done" && schedule.status === "done"
                    ? null
                    : {
                        label: t("revert"),
                        icon: AiOutlineUndo,
                        isHidden: !hasPermission("schedule workers enable"),
                        onClick: () => openModal("revert", row.original),
                      },
              ]
        }
      />

      <RevertConfirmation
        schedule={schedule}
        isOpen={!!modalData && modal === "revert"}
        onClose={closeModal}
        onSuccess={(data, response) => {
          queryClient.setQueryData(scheduleQueryKey, response);
          updateSchedule(data);
        }}
        selectedEmployee={modalData!}
      />
      <ChangeStatusConfirmation
        schedule={schedule}
        isOpen={!!modalData && modal === "changeStatus"}
        onClose={closeModal}
        onSuccess={(data, response) => {
          queryClient.setQueryData(scheduleQueryKey, response);
          updateSchedule(data);
        }}
        selectedEmployee={modalData!}
      />
      <RemoveConfirmation
        schedule={schedule}
        isOpen={!!modalData && modal === "remove"}
        onClose={closeModal}
        onSuccess={(data, response) => {
          queryClient.setQueryData(scheduleQueryKey, response);
          updateSchedule(data);
        }}
        selectedEmployee={modalData!}
      />
    </TabPanel>
  );
};

export default WorkerPanel;
