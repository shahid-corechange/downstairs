import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";
import { AiOutlineEye } from "react-icons/ai";
import { LiaLanguageSolid } from "react-icons/lia";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import Addon from "@/types/addon";
import Category from "@/types/category";
import Product from "@/types/product";
import Service from "@/types/service";

import { hasAnyPermissions, hasPermission } from "@/utils/authorization";

import { PageProps } from "@/types";

import getColumns from "./column";
import CreateModal from "./components/CreateModal";
import DeleteModal from "./components/DeleteModal";
import EditModal from "./components/EditModal";
import RestoreModal from "./components/RestoreModal";
import TranslationModal from "./components/TranslationModal";
import ViewModal from "./components/ViewModal";

type AddOnProps = {
  addons: Addon[];
  services: Service[];
  categories: Category[];
  products: Product[];
};

const defaultFilters = [{ id: "deletedAt", value: "false" }];

const AddonOverviewPage = ({
  addons,
  services,
  categories,
  products,
}: PageProps<AddOnProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<
    Addon,
    "view" | "translation"
  >();
  const [selectedRowIndex, setSelectedRowIndex] = useState<number>();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("add ons")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("add ons")} />
        </Flex>

        <DataTable
          data={addons}
          columns={columns}
          filters={defaultFilters}
          title={t("add ons")}
          withCreate={hasPermission("addons create")}
          withEdit={(row) =>
            hasPermission("addons update") && !row.original.deletedAt
          }
          withDelete={(row) =>
            hasPermission("addons delete") && !row.original.deletedAt
          }
          withRestore={hasPermission("addons restore")}
          onCreate={() => openModal("create")}
          onEdit={(row) => openModal("edit", row.original)}
          onDelete={(row) => openModal("delete", row.original)}
          onRestore={(row) => openModal("restore", row.original)}
          actions={[
            {
              label: t("view"),
              icon: AiOutlineEye,
              isHidden: (row) =>
                !!row.original.deletedAt ||
                !hasAnyPermissions(["addons read", "addon tasks index"]),
              onClick: (row) => {
                setSelectedRowIndex(row.index);
                openModal("view", row.original);
              },
            },
            {
              label: t("translations"),
              icon: LiaLanguageSolid,
              isHidden: (row) =>
                !!row.original.deletedAt ||
                !hasPermission("addon translations update"),
              onClick: (row) => openModal("translation", row.original),
            },
          ]}
          useWindowScroll
        />
      </MainLayout>
      <ViewModal
        data={
          selectedRowIndex !== undefined ? addons[selectedRowIndex] : undefined
        }
        isOpen={modal === "view"}
        onClose={closeModal}
      />
      <TranslationModal
        data={modalData}
        isOpen={modal === "translation"}
        onClose={closeModal}
      />
      <CreateModal
        services={services}
        categories={categories}
        products={products}
        isOpen={modal === "create"}
        onClose={closeModal}
      />
      <EditModal
        data={modalData}
        services={services}
        categories={categories}
        products={products}
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
    </>
  );
};

export default AddonOverviewPage;
