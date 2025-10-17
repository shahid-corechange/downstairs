import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { TbShieldCog } from "react-icons/tb";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { getEmployees } from "@/services/employee";

import User from "@/types/user";

import { hasPermission } from "@/utils/authorization";

import { PageFilterItem, PaginatedPageProps } from "@/types";

import getColumns from "./column";
import DeleteModal from "./components/DeleteModal";
import EditModal from "./components/EditModal";
import EditRoleModal from "./components/EditRoleModal";
import RestoreModal from "./components/RestoreModal";
import { EmployeeProps } from "./types";

const defaultFilters: PageFilterItem[] = [
  {
    key: "status",
    criteria: "eq",
    value: "active",
  },
];

const EmployeeOverviewPage = ({
  employees,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<EmployeeProps>) => {
  const { t } = useTranslation();

  const columns = useConst(getColumns(t));

  const { modal, modalData, openModal, closeModal } = usePageModal<
    User,
    "edit role"
  >();

  return (
    <>
      <Head>
        <title>{t("employees")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("employees")} />
        </Flex>

        <DataTable
          data={employees}
          columns={columns}
          title={t("employees")}
          sort={sort}
          filters={[...defaultFilters, ...filter.filters]}
          orFilters={filter.orFilters}
          pagination={pagination}
          fetchFn={getEmployees}
          withEdit={(row) =>
            hasPermission("employees update") && !row.original.deletedAt
          }
          withDelete={(row) =>
            hasPermission("employees delete") && !row.original.deletedAt
          }
          withRestore={hasPermission("employees restore")}
          onEdit={(row) => openModal("edit", row.original)}
          onDelete={(row) => openModal("delete", row.original)}
          onRestore={(row) => openModal("restore", row.original)}
          actions={[
            {
              label: t("edit role"),
              icon: TbShieldCog,
              isHidden: (row) =>
                !hasPermission("employee roles update") ||
                !!row.original.deletedAt,
              onClick: (row) => openModal("edit role", row.original),
            },
          ]}
          serverSide
          useWindowScroll
        />
      </MainLayout>
      <EditModal
        data={modalData}
        isOpen={modal === "edit"}
        onClose={closeModal}
      />
      <EditRoleModal
        data={modalData}
        isOpen={modal === "edit role"}
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

export default EmployeeOverviewPage;
