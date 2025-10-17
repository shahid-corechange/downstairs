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
import { LaundryOrderProduct } from "@/types/laundryOrderProduct";

import { hasPermission } from "@/utils/authorization";

import getColumns from "./column";
import AddForm from "./components/AddForm";
import EditForm from "./components/EditForm";

interface ProductPanelProps extends Omit<TabPanelProps, "laundryOrder"> {
  laundryOrder: LaundryOrder;
  onModalExpansion: (expansion: ModalExpansion) => void;
  onModalShrink: () => void;
  onRefetch: () => void;
}

const ProductPanel = ({
  laundryOrder,
  onModalExpansion,
  onModalShrink,
  onRefetch,
  ...props
}: ProductPanelProps) => {
  const { t } = useTranslation();
  const [selectedRow, setSelectedRow] = useState<LaundryOrderProduct>();
  const [isLoading, setIsLoading] = useState(false);

  const columns = useConst(getColumns(t));

  const handleDelete = () => {
    setIsLoading(true);

    router.delete(
      `/laundry-orders/${laundryOrder.id}/products/${selectedRow?.id}`,
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
        data={laundryOrder.products ?? []}
        columns={columns}
        title={t("products")}
        size="md"
        searchable={false}
        filterable={false}
        paginatable={false}
        withCreate={
          hasPermission("laundry order products create") &&
          laundryOrder.status !== "done"
        }
        onCreate={() =>
          onModalExpansion({
            title: t("add product"),
            content: (
              <AddForm
                laundryOrder={laundryOrder}
                onCancel={onModalShrink}
                onRefetch={onRefetch}
              />
            ),
          })
        }
        actions={[
          (row) =>
            hasPermission("laundry order products update") &&
            laundryOrder.status !== "done"
              ? {
                  label: t("edit"),
                  icon: FiEdit3,
                  colorScheme: "gray",
                  onClick: () =>
                    onModalExpansion({
                      title: t("edit product"),
                      content: (
                        <EditForm
                          laundryOrder={laundryOrder}
                          product={row.original}
                          onCancel={onModalShrink}
                          onRefetch={onRefetch}
                        />
                      ),
                    }),
                }
              : null,
          (row) =>
            hasPermission("laundry order products delete") &&
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
        title={t("delete prodcut")}
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
          i18nKey="product delete alert body"
          values={{ product: selectedRow?.name }}
        />
      </AlertDialog>
    </TabPanel>
  );
};

export default ProductPanel;
