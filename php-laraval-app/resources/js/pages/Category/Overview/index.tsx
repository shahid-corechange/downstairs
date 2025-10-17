import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { LiaLanguageSolid } from "react-icons/lia";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import Category from "@/types/category";

import { hasPermission } from "@/utils/authorization";

import { PageProps } from "@/types";

import getColumns from "./column";
import CreateModal from "./components/CreateModal";
import DeleteModal from "./components/DeleteModal";
import EditModal from "./components/EditModal";
import RestoreModal from "./components/RestoreModal";
import TranslationModal from "./components/TranslationModal";

type CategoryPageProps = {
  categories: Category[];
};

const defaultFilters = [{ id: "deletedAt", value: "false" }];

const CategoriesOverviewPage = ({
  categories,
}: PageProps<CategoryPageProps>) => {
  const { t } = useTranslation();
  const { modal, modalData, openModal, closeModal } = usePageModal<
    Category,
    "translations"
  >();
  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("categories")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("categories")} />
        </Flex>

        <DataTable
          data={categories}
          columns={columns}
          title={t("categories")}
          filters={defaultFilters}
          withCreate={hasPermission("categories create")}
          withEdit={(row) =>
            hasPermission("categories update") && !row.original.deletedAt
          }
          withDelete={(row) =>
            hasPermission("categories delete") && !row.original.deletedAt
          }
          withRestore={(row) =>
            hasPermission("categories restore") && !!row.original.deletedAt
          }
          onCreate={() => openModal("create")}
          onEdit={(row) => openModal("edit", row.original)}
          onDelete={(row) => openModal("delete", row.original)}
          onRestore={(row) => openModal("restore", row.original)}
          actions={[
            {
              label: t("translations"),
              icon: LiaLanguageSolid,
              isHidden: (row) =>
                !!row.original.deletedAt || !hasPermission("categories update"),
              onClick: (row) => openModal("translations", row.original),
            },
          ]}
          useWindowScroll
        />
      </MainLayout>
      <CreateModal isOpen={modal === "create"} onClose={closeModal} />
      <EditModal
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
      <TranslationModal
        data={modalData}
        isOpen={modal === "translations"}
        onClose={closeModal}
      />
    </>
  );
};

export default CategoriesOverviewPage;
