import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { Store } from "@/types/store";
import User from "@/types/user";

import { hasPermission } from "@/utils/authorization";

import { PageProps } from "@/types";

import getColumns from "./column";
import CreateModal from "./components/CreateModal";
import DeleteModal from "./components/DeleteModal";
import EditModal from "./components/EditModal";
import ProductModal from "./components/ProductModal";
import RestoreModal from "./components/RestoreModal";

export type StorePageProps = {
  stores: Store[];
  employees: User[];
};

const defaultFilters = [{ id: "deletedAt", value: "false" }];

const StoreOverviewPage = ({
  stores,
  employees,
}: PageProps<StorePageProps>) => {
  const { t } = useTranslation();
  const { modal, modalData, openModal, closeModal } = usePageModal<
    Store,
    "view" | "products"
  >();
  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("stores")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("stores")} />
        </Flex>

        <DataTable
          data={stores}
          columns={columns}
          filters={defaultFilters}
          title={t("stores")}
          withCreate={hasPermission("stores create")}
          withEdit={(row) =>
            hasPermission("stores update") && !row.original.deletedAt
          }
          withDelete={(row) =>
            hasPermission("stores delete") && !row.original.deletedAt
          }
          withRestore={(row) =>
            hasPermission("stores restore") && !!row.original.deletedAt
          }
          onCreate={() => openModal("create")}
          onEdit={(row) => openModal("edit", row.original)}
          onDelete={(row) => openModal("delete", row.original)}
          onRestore={(row) => openModal("restore", row.original)}
          actions={
            [
              // {
              //   label: t("products"),
              //   icon: AiOutlineShopping,
              //   onClick: (row) => openModal("products", row.original),
              // },
            ]
          }
          useWindowScroll
        />
      </MainLayout>
      <CreateModal
        employees={employees}
        isOpen={modal === "create"}
        onClose={closeModal}
      />
      <EditModal
        employees={employees}
        data={modalData}
        isOpen={modal === "edit"}
        onClose={closeModal}
      />
      <DeleteModal
        data={modalData}
        isOpen={modal === "delete"}
        onClose={closeModal}
      />
      <RestoreModal
        data={modalData}
        isOpen={modal === "restore"}
        onClose={closeModal}
      />
      <ProductModal
        storeId={modalData?.id}
        isOpen={modal === "products"}
        onClose={closeModal}
      />
    </>
  );
};

export default StoreOverviewPage;
