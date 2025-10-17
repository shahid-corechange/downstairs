import { Button, Flex, Icon } from "@chakra-ui/react";
import { router } from "@inertiajs/react";
import { Row } from "@tanstack/react-table";
import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { AiOutlineSwap } from "react-icons/ai";

import DataTable from "@/components/DataTable";
import Modal from "@/components/Modal";
import { ModalExpansion } from "@/components/Modal/types";

import { useGetLeaveRegistrationSchedules } from "@/services/leaveRegistration";
import { useBulkChangeWorkersMutation } from "@/services/schedule";

import { PagePagination } from "@/types/pagination";
import ScheduleEmployee from "@/types/scheduleEmployee";

import { RequestQueryStringOptions } from "@/utils/request";

import getColumns from "./column";
import BulkChangeForm from "./components/BulkChangeForm";
import ChangeForm from "./components/ChangeForm";
import { ChangedWorkers } from "./types";

interface WorkerCollisionModalProps {
  leaveRegistrationId: number;
  isOpen: boolean;
  onClose: () => void;
}

const WorkerCollisionModal = ({
  leaveRegistrationId,
  isOpen,
  onClose,
}: WorkerCollisionModalProps) => {
  const { t } = useTranslation();
  const bulkChangeWorkersMutation = useBulkChangeWorkersMutation();

  const columns = getColumns(t);

  const [isLoading, setIsLoading] = useState(false);
  const [requestOptions, setRequestOptions] = useState<
    Partial<RequestQueryStringOptions<ScheduleEmployee>>
  >({});
  const [isExpanded, setIsExpanded] = useState(false);
  const [expandableContent, setExpandableContent] = useState<React.ReactNode>();
  const [expandableTitle, setExpandableTitle] = useState<string>();
  const [changedWorkers, setChangedWorkers] = useState<ChangedWorkers[]>([]);

  const [workerCollisions, setWorkerCollisions] = useState<ScheduleEmployee[]>(
    [],
  );

  const { data, isFetching } = useGetLeaveRegistrationSchedules(
    leaveRegistrationId,
    {
      request: {
        ...requestOptions,
        only: [
          "schedule.user.fullname",
          "schedule.team.name",
          "schedule.startAt",
          "schedule.endAt",
          "user.fullname",
          "id",
          "userId",
          "scheduleId",
        ],
        include: ["schedule.user", "schedule.team", "user"],
      },
    },
  );

  const handleModalExpansion = (expansion: ModalExpansion) => {
    setIsExpanded(true);
    setExpandableContent(expansion.content);
    setExpandableTitle(expansion.title);
  };

  const handleShrink = () => {
    setIsExpanded(false);
    setExpandableContent(undefined);
    setExpandableTitle(undefined);
  };

  const handleChangeWorker = (worker: ScheduleEmployee, userId: number) => {
    setChangedWorkers((prev) => [
      ...prev,
      { scheduleEmployeeId: worker.id, scheduleId: worker.scheduleId, userId },
    ]);

    setWorkerCollisions((prev) => prev.filter((item) => item.id !== worker.id));
  };

  const handleChangeWorkers = (
    selectedScheduleEmployees: ScheduleEmployee[],
    userId: number,
  ) => {
    selectedScheduleEmployees.forEach((worker) => {
      setChangedWorkers((prev) => [
        ...prev,
        {
          scheduleEmployeeId: worker.id,
          scheduleId: worker.scheduleId,
          userId,
        },
      ]);
    });

    setWorkerCollisions((prev) =>
      prev.filter((item) => !selectedScheduleEmployees.includes(item)),
    );
  };

  const handleSubmit = () => {
    setIsLoading(true);

    bulkChangeWorkersMutation.mutate(
      {
        changes: changedWorkers,
      },
      {
        onSuccess: () => {
          router.reload();
          onClose();
        },
        onError: () => {
          setIsLoading(false);
        },
      },
    );
  };

  useEffect(() => {
    if (!isOpen) {
      setIsLoading(false);
      return;
    }

    if (!isFetching) {
      setWorkerCollisions(data?.data ?? []);
    }
  }, [isOpen, data?.data, isFetching]);

  return (
    <Modal
      size="5xl"
      bodyContainer={{ p: 8 }}
      title={t("worker schedules")}
      isOpen={isOpen}
      onClose={onClose}
      onShrink={handleShrink}
      expandableContent={expandableContent}
      expandableTitle={expandableTitle}
      isExpanded={isExpanded}
    >
      <DataTable
        data={workerCollisions}
        columns={columns}
        fetchFn={(options) => setRequestOptions(options)}
        isFetching={isFetching}
        pagination={data?.pagination as PagePagination}
        size="md"
        serverSide
        useWindowScroll
        selectionActions={[
          {
            label: t("bulk change"),
            colorScheme: "brand",
            leftIcon: <Icon as={AiOutlineSwap} />,
            onClick: (rows) => {
              const selectedScheduleEmployees = rows.map(
                (item: Row<ScheduleEmployee>) => item.original,
              );

              handleModalExpansion({
                title: t("bulk change worker"),
                content: (
                  <BulkChangeForm
                    selectedScheduleEmployees={selectedScheduleEmployees}
                    onSubmit={(userId) => {
                      handleChangeWorkers(selectedScheduleEmployees, userId);
                    }}
                    onClose={handleShrink}
                  />
                ),
              });
            },
          },
        ]}
        actions={[
          {
            label: t("change"),
            icon: AiOutlineSwap,
            onClick: (row) =>
              handleModalExpansion({
                title: t("change worker"),
                content: (
                  <ChangeForm
                    scheduleEmployee={row.original}
                    onSubmit={(userId) =>
                      handleChangeWorker(row.original, userId)
                    }
                    onClose={handleShrink}
                  />
                ),
              }),
          },
        ]}
      />
      <Flex justify="right" mt={4} gap={4}>
        <Button colorScheme="gray" fontSize="sm" onClick={onClose}>
          {t("close")}
        </Button>
        <Button
          fontSize="sm"
          loadingText={t("please wait")}
          isLoading={isLoading}
          isDisabled={workerCollisions.length > 0}
          onClick={handleSubmit}
        >
          {t("continue reschedule")}
        </Button>
      </Flex>
    </Modal>
  );
};

export default WorkerCollisionModal;
