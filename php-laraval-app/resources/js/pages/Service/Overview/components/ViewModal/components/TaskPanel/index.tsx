import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";
import DataTable from "@/components/DataTable";
import { ModalExpansion } from "@/components/Modal/types";

import CustomTask from "@/types/customTask";
import Service from "@/types/service";

import { hasPermission } from "@/utils/authorization";

import getColumns from "./column";
import AddForm from "./components/AddForm";
import EditForm from "./components/EditForm";

interface TaskPanelProps extends TabPanelProps {
  service?: Service;
  onModalExpansion: (expansion: ModalExpansion) => void;
  onModalShrink: () => void;
}

const TaskPanel = ({
  service,
  onModalExpansion,
  onModalShrink,
  ...props
}: TaskPanelProps) => {
  const { t } = useTranslation();
  const columns = useConst(getColumns(t));
  const [selectedTask, setSelectedTask] = useState<CustomTask>();
  const [isLoading, setIsLoading] = useState(false);

  const handleDelete = () => {
    setIsLoading(true);

    router.delete(`/services/${service?.id}/tasks/${selectedTask?.id}`, {
      onFinish: () => setIsLoading(false),
      onSuccess: () => setSelectedTask(undefined),
    });
  };

  return (
    <TabPanel {...props}>
      <DataTable
        data={service?.tasks ?? []}
        columns={columns}
        title={t("task")}
        size="md"
        searchable={false}
        filterable={false}
        paginatable={false}
        withCreate={hasPermission("service tasks create")}
        withEdit={hasPermission("service tasks update")}
        withDelete={hasPermission("service tasks delete")}
        onCreate={() =>
          onModalExpansion({
            content: <AddForm service={service} onCancel={onModalShrink} />,
            title: t("add task"),
          })
        }
        onEdit={(row) =>
          onModalExpansion({
            title: t("edit task"),
            content: (
              <EditForm
                service={service}
                task={row.original}
                onCancel={onModalShrink}
              />
            ),
          })
        }
        onDelete={(row) => setSelectedTask(row.original)}
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
