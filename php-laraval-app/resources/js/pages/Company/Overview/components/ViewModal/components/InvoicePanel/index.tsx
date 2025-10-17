import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { useMemo } from "react";
import { useTranslation } from "react-i18next";

import DataTable from "@/components/DataTable";
import { ModalExpansion } from "@/components/Modal/types";

import { usePageModal } from "@/hooks/modal";

import Customer from "@/types/customer";

import { hasPermission } from "@/utils/authorization";

import getColumns from "./column";
import AddForm from "./components/AddForm";
import DeleteModal from "./components/DeleteModal";
import EditForm from "./components/EditForm";
import RestoreModal from "./components/RestoreModal";

interface InvoicePanelProps extends TabPanelProps {
  companyId: number;
  customers: Customer[];
  onModalExpansion: (expansion: ModalExpansion) => void;
  onModalShrink: () => void;
  onRefetch: () => void;
}

const InvoicePanel = ({
  companyId,
  customers,
  onModalExpansion,
  onModalShrink,
  onRefetch,
  ...props
}: InvoicePanelProps) => {
  const { t } = useTranslation();
  const { modal, modalData, openModal, closeModal } = usePageModal<Customer>();
  const columns = useConst(getColumns(t));

  const customer = useMemo(
    () => customers.find((customer) => customer.type === "primary"),
    [customers],
  );
  const invoices = useMemo(
    () => customers.filter((customer) => customer.type === "invoice") ?? [],
    [customers],
  );

  return (
    <TabPanel {...props}>
      <DataTable
        data={invoices}
        columns={columns}
        title={t("address")}
        size="md"
        searchable={false}
        filterable={false}
        paginatable={false}
        withCreate={hasPermission("company invoice addresses create")}
        withEdit={(row) =>
          hasPermission("company invoice addresses update") &&
          !row.original.deletedAt
        }
        withDelete={(row) =>
          hasPermission("company invoice addresses delete") &&
          !row.original.deletedAt
        }
        withRestore={hasPermission("company invoice addresses restore")}
        onCreate={() =>
          onModalExpansion({
            content: (
              <AddForm
                primaryAddress={customer}
                companyId={companyId}
                onCancel={onModalShrink}
                onRefetch={onRefetch}
              />
            ),
            title: t("add address"),
          })
        }
        onEdit={(row) =>
          onModalExpansion({
            title: t("edit address"),
            content: (
              <EditForm
                primaryAddress={customer}
                companyId={companyId}
                customer={row.original}
                onCancel={onModalShrink}
                onRefetch={onRefetch}
              />
            ),
          })
        }
        onDelete={(row) => openModal("delete", row.original)}
        onRestore={(row) => openModal("restore", row.original)}
      />

      <DeleteModal
        companyId={companyId}
        customer={modalData}
        isOpen={modal === "delete"}
        onClose={closeModal}
        onRefetch={onRefetch}
      />
      <RestoreModal
        companyId={companyId}
        customer={modalData}
        isOpen={modal === "restore"}
        onClose={closeModal}
        onRefetch={onRefetch}
      />
    </TabPanel>
  );
};

export default InvoicePanel;
