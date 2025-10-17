import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { t } from "i18next";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { getProperties } from "@/services/property";

import Property from "@/types/property";

import { hasPermission } from "@/utils/authorization";

import { PageFilterItem, PaginatedPageProps } from "@/types";

import getColumns from "./columns";
import DeleteModal from "./components/DeleteModal";
import EditModal from "./components/EditModal";
import RestoreModal from "./components/RestoreModal";

type PropertyProps = {
  properties: Property[];
};

const defaultFilters: PageFilterItem[] = [
  {
    key: "status",
    criteria: "eq",
    value: "active",
  },
];

const PropertyOverviewPage = ({
  properties,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<PropertyProps>) => {
  const { modal, modalData, openModal, closeModal } = usePageModal<Property>();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("properties")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("properties")} />
        </Flex>

        <DataTable
          data={properties}
          columns={columns}
          title={t("properties")}
          fetchFn={getProperties}
          sort={sort}
          filters={[...defaultFilters, ...filter.filters]}
          orFilters={filter.orFilters}
          pagination={pagination}
          withEdit={(row) =>
            hasPermission("properties update") && !row.original.deletedAt
          }
          withDelete={(row) =>
            hasPermission("properties delete") && !row.original.deletedAt
          }
          withRestore={hasPermission("properties restore")}
          onEdit={(row) => openModal("edit", row.original)}
          onDelete={(row) => openModal("delete", row.original)}
          onRestore={(row) => openModal("restore", row.original)}
          serverSide
          useWindowScroll
        />
      </MainLayout>
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
    </>
  );
};

export default PropertyOverviewPage;
