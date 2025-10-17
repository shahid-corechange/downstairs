import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { AiOutlineEye } from "react-icons/ai";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { getCompanies } from "@/services/company";

import Customer from "@/types/customer";

import { hasAnyPermissions, hasPermission } from "@/utils/authorization";

import { PageFilterItem, PaginatedPageProps } from "@/types";

import getColumns from "./column";
import DeleteModal from "./components/DeleteModal";
import EditModal from "./components/EditModal";
import RestoreModal from "./components/RestoreModal";
import ViewModal from "./components/ViewModal";
import { CompanyPageProps } from "./types";

const defaultFilters: PageFilterItem[] = [
  {
    key: "deletedAt",
    criteria: "eq",
    value: false,
  },
];

const CompanyOverviewPage = ({
  companies,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<CompanyPageProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<
    Customer,
    "view"
  >();
  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("companies")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("companies")} />
        </Flex>

        <DataTable
          data={companies}
          columns={columns}
          title={t("companies")}
          sort={sort}
          filters={[...defaultFilters, ...filter.filters]}
          orFilters={filter.orFilters}
          pagination={pagination}
          fetchFn={getCompanies}
          withEdit={(row) =>
            hasPermission("companies update") && !row.original.deletedAt
          }
          withDelete={(row) =>
            hasPermission("companies delete") && !row.original.deletedAt
          }
          withRestore={hasPermission("companies restore")}
          onEdit={(row) => openModal("edit", row.original)}
          onDelete={(row) => openModal("delete", row.original)}
          onRestore={(row) => openModal("restore", row.original)}
          actions={[
            {
              label: t("view"),
              icon: AiOutlineEye,
              isHidden: (row) =>
                !hasAnyPermissions([
                  "companies primary address read",
                  "company invoice addresses index",
                ]) || !!row.original.deletedAt,
              onClick: (row) => {
                openModal("view", row.original);
              },
            },
          ]}
          serverSide
          useWindowScroll
        />
      </MainLayout>
      <ViewModal
        companyId={modalData?.id}
        user={modalData?.companyUser}
        isOpen={modal === "view"}
        onClose={closeModal}
      />
      <EditModal
        data={modalData}
        user={modalData?.companyUser}
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

export default CompanyOverviewPage;
