import {
  Button,
  Flex,
  Icon,
  TabPanel,
  TabPanelProps,
  useConst,
} from "@chakra-ui/react";
import { QueryKey } from "@tanstack/react-query";
import { useMemo, useState } from "react";
import { Trans, useTranslation } from "react-i18next";
import { FiEdit3 } from "react-icons/fi";
import { LuPlus, LuTrash } from "react-icons/lu";

import Alert from "@/components/Alert";
import AlertDialog from "@/components/AlertDialog";
import AuthorizationGuard from "@/components/AuthorizationGuard";
import DataTable from "@/components/DataTable";
import { ModalExpansion } from "@/components/Modal/types";

import { queryClient } from "@/services/client";
import { useDeleteScheduleTaskMutation } from "@/services/schedule";

import CustomTask from "@/types/customTask";
import Schedule, { ScheduleItem, ScheduleTask } from "@/types/schedule";

import { hasPermission } from "@/utils/authorization";
import { isReadonly } from "@/utils/schedule";

import getColumns from "./column";
import AddForm from "./components/AddForm";
import EditForm from "./components/EditForm";

interface TaskPanelProps extends TabPanelProps {
  schedule: Schedule;
  scheduleQueryKey: QueryKey;
  onModalExpansion: (expansion: ModalExpansion) => void;
  onModalShrink: () => void;
}

const TaskPanel = ({
  schedule,
  scheduleQueryKey,
  onModalExpansion,
  onModalShrink,
  ...props
}: TaskPanelProps) => {
  const { t } = useTranslation();

  const deleteTaskMutation = useDeleteScheduleTaskMutation();

  const [selectedTask, setSelectedTask] = useState<ScheduleTask>();
  const [isLoading, setIsLoading] = useState(false);

  const columns = useConst(getColumns(t));

  const tasks = useMemo(() => {
    const result: ScheduleTask[] = [];
    const serviceTasks: ScheduleTask[] = schedule?.service?.tasks ?? [];
    const scheduleItems: ScheduleItem[] = schedule?.items ?? [];
    const subscriptionTasks: ScheduleTask[] =
      schedule?.subscription?.tasks ?? [];
    const scheduleTasks = schedule?.tasks ?? [];

    serviceTasks.forEach(({ id, name, description }) => {
      result.push({
        id,
        name,
        description,
        source: "service",
      });
    });

    scheduleItems.forEach((item) => {
      const itemTasks: CustomTask[] = item.item?.tasks ?? [];

      itemTasks.forEach(({ id, name, description }) => {
        result.push({
          id,
          name,
          description,
          source:
            item.itemableType === "App\\Models\\Addon" ? "add on" : "product",
        });
      });
    });

    subscriptionTasks.forEach(({ id, name, description }) => {
      result.push({
        id,
        name,
        description,
        source: "subscription",
      });
    });

    scheduleTasks.forEach(({ id, name, description, translations }) => {
      result.push({
        id,
        name,
        description,
        translations,
        source: "schedule",
      });
    });

    return result;
  }, [schedule]);

  const handleDelete = () => {
    setIsLoading(true);

    deleteTaskMutation.mutate(
      {
        scheduleId: schedule.id,
        taskId: selectedTask!.id,
      },
      {
        onSettled: () => setIsLoading(false),
        onSuccess: ({ response }) => {
          setSelectedTask(undefined);
          queryClient.setQueryData(scheduleQueryKey, response);
        },
      },
    );
  };

  return (
    <TabPanel {...props}>
      {!isReadonly(schedule) && (
        <AuthorizationGuard permissions="schedule tasks create">
          <Alert
            status="info"
            title={t("info")}
            message={t("schedule task info")}
            fontSize="small"
            mb={6}
          />
          <Flex justify="flex-end">
            <Button
              size="sm"
              fontSize="small"
              leftIcon={<Icon as={LuPlus} boxSize={4} />}
              onClick={() =>
                onModalExpansion({
                  content: (
                    <AddForm
                      schedule={schedule}
                      scheduleQueryKey={scheduleQueryKey}
                      onCancel={onModalShrink}
                    />
                  ),
                  title: t("add task"),
                })
              }
            >
              {t("new resource", { resource: t("task") })}
            </Button>
          </Flex>
        </AuthorizationGuard>
      )}

      <DataTable
        data={tasks}
        columns={columns}
        size="md"
        searchable={false}
        filterable={false}
        paginatable={false}
        actions={
          !isReadonly(schedule)
            ? [
                (row) =>
                  row.original.source === "schedule"
                    ? {
                        label: t("edit"),
                        icon: FiEdit3,
                        isHidden: !hasPermission("schedule tasks update"),
                        onClick: (row) =>
                          onModalExpansion({
                            content: (
                              <EditForm
                                schedule={schedule}
                                task={row.original}
                                scheduleQueryKey={scheduleQueryKey}
                                onCancel={onModalShrink}
                              />
                            ),
                            title: t("edit task"),
                          }),
                      }
                    : null,
                (row) =>
                  row.original.source === "schedule"
                    ? {
                        label: t("delete"),
                        icon: LuTrash,
                        colorScheme: "red",
                        color: "red.500",
                        _dark: { color: "red.200" },
                        isHidden: !hasPermission("schedule tasks delete"),
                        onClick: (row) => setSelectedTask(row.original),
                      }
                    : null,
              ]
            : []
        }
      />

      <AlertDialog
        title={t("delete task")}
        confirmButton={{
          isLoading,
          colorScheme: "red",
          loadingText: t("please wait"),
        }}
        confirmText={t("delete")}
        isOpen={!!selectedTask}
        onClose={() => setSelectedTask(undefined)}
        onConfirm={handleDelete}
      >
        <Trans
          i18nKey="task delete alert body"
          values={{ task: selectedTask?.name }}
        />
      </AlertDialog>
    </TabPanel>
  );
};

export default TaskPanel;
