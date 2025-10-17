import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";
import { FiEdit3 } from "react-icons/fi";
import { LuTrash } from "react-icons/lu";

import AlertDialog from "@/components/AlertDialog";
import DataTable from "@/components/DataTable";
import { ModalExpansion } from "@/components/Modal/types";

import { LaundryOrder } from "@/types/laundryOrder";
import { LaundryOrderSchedule } from "@/types/laundryOrderSchedule";

import { hasPermission } from "@/utils/authorization";

import getColumns from "./column";
import AddForm from "./components/AddForm";
import EditForm from "./components/EditForm";

interface SchedulePanelProps extends Omit<TabPanelProps, "laundryOrder"> {
  laundryOrder: LaundryOrder;
  onModalExpansion: (expansion: ModalExpansion) => void;
  onModalShrink: () => void;
  onRefetch: () => void;
}

const SchedulePanel = ({
  laundryOrder,
  onModalExpansion,
  onModalShrink,
  onRefetch,
  ...props
}: SchedulePanelProps) => {
  const { t } = useTranslation();
  const [selectedRow, setSelectedRow] = useState<LaundryOrderSchedule>();
  const [isLoading, setIsLoading] = useState(false);

  const columns = useConst(getColumns(t));

  const handleDelete = () => {
    setIsLoading(true);

    router.delete(
      `/laundry-orders/${laundryOrder.id}/schedules/${selectedRow?.id}`,
      {
        onFinish: () => setIsLoading(false),
        onSuccess: () => {
          setSelectedRow(undefined);
          onRefetch();
        },
      },
    );
  };

  return (
    <TabPanel {...props}>
      <DataTable
        data={laundryOrder.schedules ?? []}
        columns={columns}
        title={t("schedules")}
        size="md"
        searchable={false}
        filterable={false}
        paginatable={false}
        withCreate={
          hasPermission("laundry order schedules create") &&
          laundryOrder.status !== "done"
        }
        onCreate={() =>
          onModalExpansion({
            title: t("add schedule"),
            content: (
              <AddForm
                laundryOrderId={laundryOrder.id}
                onCancel={onModalShrink}
                onRefetch={onRefetch}
              />
            ),
          })
        }
        actions={[
          (row) =>
            hasPermission("laundry order schedules update") &&
            laundryOrder.status !== "done"
              ? {
                  label: t("edit"),
                  icon: FiEdit3,
                  colorScheme: "gray",
                  onClick: () =>
                    onModalExpansion({
                      title: t("edit schedule"),
                      content: (
                        <EditForm
                          laundryOrderId={laundryOrder.id}
                          laundryOrderSchedule={row.original}
                          onCancel={onModalShrink}
                          onRefetch={onRefetch}
                        />
                      ),
                    }),
                }
              : null,
          (row) =>
            hasPermission("laundry order schedules delete") &&
            laundryOrder.status !== "done"
              ? {
                  label: t("delete"),
                  icon: LuTrash,
                  colorScheme: "red",
                  color: "red.500",
                  _dark: { color: "red.200" },
                  onClick: () => setSelectedRow(row.original),
                }
              : null,
        ]}
      />

      <AlertDialog
        title={t("delete schedule")}
        confirmButton={{
          isLoading,
          colorScheme: "red",
          loadingText: t("please wait"),
        }}
        confirmText={t("delete")}
        isOpen={!!selectedRow}
        onClose={() => setSelectedRow(undefined)}
        onConfirm={handleDelete}
      >
        <Trans
          i18nKey="schedule delete alert body"
          values={{ schedule: selectedRow?.type }}
        />
      </AlertDialog>
    </TabPanel>
  );
};

export default SchedulePanel;
