import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";
import DataTable from "@/components/DataTable";
import { ModalExpansion } from "@/components/Modal/types";

import Addon from "@/types/addon";
import CustomTask from "@/types/customTask";

import { hasPermission } from "@/utils/authorization";

import getColumns from "./column";
import AddForm from "./components/AddForm";
import EditForm from "./components/EditForm";

interface TaskPanelProps extends TabPanelProps {
  addOn?: Addon;
  onModalExpansion: (expansion: ModalExpansion) => void;
  onModalShrink: () => void;
}

const TaskPanel = ({
  addOn,
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

    router.delete(`/addons/${addOn?.id}/tasks/${selectedTask?.id}`, {
      onFinish: () => setIsLoading(false),
      onSuccess: () => setSelectedTask(undefined),
    });
  };

  return (
    <TabPanel {...props}>
      <DataTable
        data={addOn?.tasks ?? []}
        columns={columns}
        size="md"
        searchable={false}
        filterable={false}
        paginatable={false}
        withCreate={hasPermission("addon tasks create")}
        withEdit={hasPermission("addon tasks update")}
        withDelete={hasPermission("addon tasks delete")}
        onCreate={() =>
          onModalExpansion({
            content: <AddForm addOn={addOn} onCancel={onModalShrink} />,
            title: t("add task"),
          })
        }
        onEdit={(row) =>
          onModalExpansion({
            title: t("edit task"),
            content: (
              <EditForm
                addOn={addOn}
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
