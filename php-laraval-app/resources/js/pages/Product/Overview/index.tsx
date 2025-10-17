import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
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
import { Store } from "@/types/store";

import { hasPermission } from "@/utils/authorization";

import { PageProps } from "@/types";

import getColumns from "./column";
import CreateModal from "./components/CreateModal";
import DeleteModal from "./components/DeleteModal";
import EditModal from "./components/EditModal";
import RestoreModal from "./components/RestoreModal";
import TranslationModal from "./components/TranslationModal";

type ProductProps = {
  products: Product[];
  categories: Category[];
  addons: Addon[];
  services: Service[];
  stores: Store[];
};

const defaultFilters = [{ id: "deletedAt", value: "false" }];

const ProductOverviewPage = ({
  products,
  categories,
  addons,
  services,
  stores,
}: PageProps<ProductProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<
    Product,
    "translation"
  >();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("products")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("products")} />
        </Flex>

        <DataTable
          data={products}
          columns={columns}
          filters={defaultFilters}
          title={t("products")}
          withCreate={hasPermission("products create")}
          withEdit={(row) =>
            hasPermission("products update") && !row.original.deletedAt
          }
          withDelete={(row) =>
            hasPermission("products delete") && !row.original.deletedAt
          }
          withRestore={hasPermission("products restore")}
          onCreate={() => openModal("create")}
          onEdit={(row) => openModal("edit", row.original)}
          onDelete={(row) => openModal("delete", row.original)}
          onRestore={(row) => openModal("restore", row.original)}
          actions={[
            {
              label: t("translations"),
              icon: LiaLanguageSolid,
              isHidden: (row) =>
                !!row.original.deletedAt ||
                !hasPermission("product translations update"),
              onClick: (row) => openModal("translation", row.original),
            },
          ]}
          useWindowScroll
        />
      </MainLayout>
      <CreateModal
        addons={addons}
        services={services}
        stores={stores}
        categories={categories}
        isOpen={modal === "create"}
        onClose={closeModal}
      />
      <EditModal
        data={modalData}
        addons={addons}
        services={services}
        stores={stores}
        categories={categories}
        isOpen={modal === "edit"}
        onClose={closeModal}
      />
      <TranslationModal
        data={modalData}
        isOpen={modal === "translation"}
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

export default ProductOverviewPage;
