import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";
import DataTable from "@/components/DataTable";
import { ModalExpansion } from "@/components/Modal/types";

import CustomTask from "@/types/customTask";
import Subscription from "@/types/subscription";

import { hasPermission } from "@/utils/authorization";

import getColumns from "./column";
import AddForm from "./components/AddForm";
import EditForm from "./components/EditForm";

interface TaskPanelProps extends TabPanelProps {
  subscription: Subscription;
  onModalExpansion: (expansion: ModalExpansion) => void;
  onModalShrink: () => void;
  onRefetch: () => void;
}

const TaskPanel = ({
  subscription,
  onModalExpansion,
  onModalShrink,
  onRefetch,
  ...props
}: TaskPanelProps) => {
  const { t } = useTranslation();
  const [selectedTask, setSelectedTask] = useState<CustomTask>();
  const [isLoading, setIsLoading] = useState(false);

  const columns = useConst(getColumns(t));

  const handleDelete = () => {
    setIsLoading(true);

    router.delete(
      `/companies/subscriptions/${subscription.id}/tasks/${selectedTask?.id}`,
      {
        onFinish: () => setIsLoading(false),
        onSuccess: () => {
          setSelectedTask(undefined);
          onRefetch();
        },
      },
    );
  };

  return (
    <TabPanel {...props}>
      <DataTable
        data={subscription.tasks ?? []}
        columns={columns}
        title={t("task")}
        size="md"
        searchable={false}
        filterable={false}
        paginatable={false}
        withCreate={hasPermission("company subscription tasks create")}
        withEdit={hasPermission("company subscription tasks update")}
        withDelete={hasPermission("company subscription tasks delete")}
        onCreate={() =>
          onModalExpansion({
            content: (
              <AddForm
                subscription={subscription}
                onCancel={onModalShrink}
                onRefetch={onRefetch}
              />
            ),
            title: t("add task"),
          })
        }
        onEdit={(row) =>
          onModalExpansion({
            title: t("edit task"),
            content: (
              <EditForm
                subscription={subscription}
                task={row.original}
                onCancel={onModalShrink}
                onRefetch={onRefetch}
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
