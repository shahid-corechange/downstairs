import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { router } from "@inertiajs/react";
import { useMemo, useState } from "react";
import { Trans, useTranslation } from "react-i18next";
import { FiEdit3 } from "react-icons/fi";
import { LuTrash } from "react-icons/lu";

import Alert from "@/components/Alert";
import AlertDialog from "@/components/AlertDialog";
import DataTable from "@/components/DataTable";
import { ModalExpansion } from "@/components/Modal/types";

import Order, { OrderRow } from "@/types/order";

import { hasPermission } from "@/utils/authorization";

import getColumns from "./column";
import AddForm from "./components/AddForm";
import EditForm from "./components/EditForm";

interface RowPanelProps extends Omit<TabPanelProps, "order"> {
  order: Order;
  extraArticleIds: number[];
  onModalExpansion: (expansion: ModalExpansion) => void;
  onModalShrink: () => void;
  onRefetch: () => void;
}

const RowPanel = ({
  order,
  extraArticleIds,
  onModalExpansion,
  onModalShrink,
  onRefetch,
  ...props
}: RowPanelProps) => {
  const { t } = useTranslation();
  const [selectedRow, setSelectedRow] = useState<OrderRow>();
  const [isLoading, setIsLoading] = useState(false);

  const columns = useConst(getColumns(t));
  const readonlyRows = useMemo(() => {
    if (!order.fixedPrice) {
      return {};
    }

    const productArticleIds = order?.subscription?.products?.flatMap(
      (product) => product.fortnoxArticleId,
    );
    const articleIds = [
      order?.service?.fortnoxArticleId,
      ...(productArticleIds ?? []),
      ...extraArticleIds,
    ];

    return order?.rows?.reduce<Record<number, boolean>>((acc, row) => {
      acc[row.id] = articleIds.includes(row.fortnoxArticleId);
      return acc;
    }, {});
  }, [order, extraArticleIds]);

  const handleDelete = () => {
    setIsLoading(true);

    router.delete(`/orders/${order.id}/rows/${selectedRow?.id}`, {
      onFinish: () => setIsLoading(false),
      onSuccess: () => {
        setSelectedRow(undefined);
        onRefetch();
      },
    });
  };

  return (
    <TabPanel {...props}>
      <Alert
        status="info"
        title={t("info")}
        message={t("order row info")}
        fontSize="small"
        mb={6}
      />
      <DataTable
        data={order?.rows ?? []}
        columns={columns}
        title={t("row")}
        size="md"
        searchable={false}
        filterable={false}
        paginatable={false}
        withCreate={
          hasPermission("order rows create") && order.status === "draft"
        }
        onCreate={() =>
          onModalExpansion({
            title: t("add row"),
            content: (
              <AddForm
                order={order}
                onCancel={onModalShrink}
                onRefetch={onRefetch}
              />
            ),
          })
        }
        actions={[
          (row) =>
            !readonlyRows?.[row.original.id] &&
            hasPermission("order rows update") &&
            order.status === "draft"
              ? {
                  label: t("edit"),
                  icon: FiEdit3,
                  colorScheme: "gray",
                  onClick: () =>
                    onModalExpansion({
                      title: t("edit row"),
                      content: (
                        <EditForm
                          order={order}
                          row={row.original}
                          onCancel={onModalShrink}
                          onRefetch={onRefetch}
                        />
                      ),
                    }),
                }
              : null,
          (row) =>
            !readonlyRows?.[row.original.id] &&
            hasPermission("order rows delete") &&
            order.status === "draft"
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
        title={t("delete row")}
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
          i18nKey="row delete alert body"
          values={{ row: selectedRow?.description }}
        />
      </AlertDialog>
    </TabPanel>
  );
};

export default RowPanel;
