import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { AiOutlineEye } from "react-icons/ai";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { getCustomers } from "@/services/customer";

import User from "@/types/user";

import { hasAnyPermissions, hasPermission } from "@/utils/authorization";

import { PageFilterItem, PaginatedPageProps } from "@/types";

import getColumns from "./column";
import DeleteModal from "./components/DeleteModal";
import RestoreModal from "./components/RestoreModal";
import ViewModal from "./components/ViewModal";
import { CustomerPageProps } from "./types";

const defaultFilters: PageFilterItem[] = [
  {
    key: "status",
    criteria: "eq",
    value: "active",
  },
];

const CustomerOverviewPage = ({
  customers,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<CustomerPageProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<
    User,
    "view"
  >();
  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("customers")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("customers")} />
        </Flex>

        <DataTable
          data={customers}
          columns={columns}
          title={t("customers")}
          sort={sort}
          filters={[...defaultFilters, ...filter.filters]}
          orFilters={filter.orFilters}
          pagination={pagination}
          fetchFn={getCustomers}
          withDelete={(row) =>
            hasPermission("customers delete") && !row.original.deletedAt
          }
          withRestore={hasPermission("customers restore")}
          onDelete={(row) => openModal("delete", row.original)}
          onRestore={(row) => openModal("restore", row.original)}
          actions={[
            {
              label: t("view"),
              icon: AiOutlineEye,
              isHidden: (row) =>
                !hasAnyPermissions([
                  "customers primary address read",
                  "customer invoice addresses index",
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
        data={modalData}
        isOpen={modal === "view"}
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

export default CustomerOverviewPage;
