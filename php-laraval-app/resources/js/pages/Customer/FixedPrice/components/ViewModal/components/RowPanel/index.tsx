import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { router } from "@inertiajs/react";
import { useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import AlertDialog from "@/components/AlertDialog";
import DataTable from "@/components/DataTable";
import { ModalExpansion } from "@/components/Modal/types";

import FixedPrice, { FixedPriceRow } from "@/types/fixedPrice";

import { hasPermission } from "@/utils/authorization";

import getColumns from "./column";
import AddForm from "./components/AddForm";
import EditForm from "./components/EditForm";

interface RowPanelProps extends TabPanelProps {
  fixedPrice: FixedPrice;
  onModalExpansion: (expansion: ModalExpansion) => void;
  onModalShrink: () => void;
  onRefetch: () => void;
}

const RowPanel = ({
  fixedPrice,
  onModalExpansion,
  onModalShrink,
  onRefetch,
  ...props
}: RowPanelProps) => {
  const { t } = useTranslation();
  const [selectedRow, setSelectedRow] = useState<FixedPriceRow>();
  const [isLoading, setIsLoading] = useState(false);

  const columns = useConst(getColumns(t));

  const handleDelete = () => {
    setIsLoading(true);

    router.delete(
      `/customers/fixedprices/${fixedPrice.id}/rows/${selectedRow?.id}`,
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
      <Alert
        status="info"
        title={t("info")}
        message={t("fixed prices row info")}
        fontSize="small"
        mb={6}
      />
      <DataTable
        data={fixedPrice.rows ?? []}
        columns={columns}
        title={t("row")}
        size="md"
        searchable={false}
        filterable={false}
        paginatable={false}
        withCreate={hasPermission("fixed price rows create")}
        withEdit={hasPermission("fixed price rows update")}
        withDelete={hasPermission("fixed price rows delete")}
        onCreate={() =>
          onModalExpansion({
            title: t("add row"),
            content: (
              <AddForm
                fixedPrice={fixedPrice}
                onCancel={onModalShrink}
                onRefetch={onRefetch}
              />
            ),
          })
        }
        onEdit={(row) =>
          onModalExpansion({
            title: t("edit row"),
            content: (
              <EditForm
                fixedPrice={fixedPrice}
                row={row.original}
                onCancel={onModalShrink}
                onRefetch={onRefetch}
              />
            ),
          })
        }
        onDelete={(row) => setSelectedRow(row.original)}
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
          values={{ row: t(selectedRow?.type ?? "") }}
        />
      </AlertDialog>
    </TabPanel>
  );
};

export default RowPanel;
