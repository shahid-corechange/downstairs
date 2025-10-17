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

type ServiceProps = {
  services: Service[];
  categories: Category[];
  addons: Addon[];
  products: Product[];
};

const defaultFilters = [{ id: "deletedAt", value: "false" }];

const ServiceOverviewPage = ({
  services,
  categories,
  addons,
  products,
}: PageProps<ServiceProps>) => {
  const { t } = useTranslation();
  const columns = useConst(getColumns(t));

  const { modal, modalData, openModal, closeModal } = usePageModal<
    Service,
    "view" | "translation"
  >();
  const [selectedRowIndex, setSelectedRowIndex] = useState<number>();

  return (
    <>
      <Head>
        <title>{t("services")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("services")} />
        </Flex>

        <DataTable
          data={services}
          columns={columns}
          filters={defaultFilters}
          title={t("services")}
          withCreate={hasPermission("services create")}
          withEdit={(row) =>
            hasPermission("services update") && !row.original.deletedAt
          }
          withDelete={(row) =>
            hasPermission("services delete") && !row.original.deletedAt
          }
          withRestore={hasPermission("services restore")}
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
                !hasAnyPermissions(["services read", "service tasks index"]),
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
                !hasPermission("service translations update"),
              onClick: (row) => openModal("translation", row.original),
            },
          ]}
          useWindowScroll
        />
      </MainLayout>
      <ViewModal
        data={
          selectedRowIndex !== undefined
            ? services[selectedRowIndex]
            : undefined
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
        isOpen={modal === "create"}
        categories={categories}
        addons={addons}
        products={products}
        onClose={closeModal}
      />
      <EditModal
        data={modalData}
        categories={categories}
        addons={addons}
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

export default ServiceOverviewPage;
