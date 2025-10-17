import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { Role } from "@/types/authorization";

import { hasPermission } from "@/utils/authorization";

import { PageProps } from "@/types";

import getColumns from "./column";
import CreateModal from "./components/CreateModal";
import DeleteModal from "./components/DeleteModal";
import EditModal from "./components/EditModal";

type RoleOverviewPageProps = {
  roles: Role[];
};

const RoleOverviewPage = ({ roles }: PageProps<RoleOverviewPageProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<Role>();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("roles")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("roles")} />
        </Flex>

        <DataTable
          data={roles}
          columns={columns}
          title={t("roles")}
          withCreate={hasPermission("roles create")}
          withEdit={(row) =>
            hasPermission("roles update") && row.original.name !== "Superadmin"
          }
          withDelete={(row) =>
            hasPermission("roles delete") &&
            !["Superadmin", "Customer", "Employee"].includes(row.original.name)
          }
          onCreate={() => openModal("create")}
          onEdit={(row) => openModal("edit", row.original)}
          onDelete={(row) => openModal("delete", row.original)}
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
    </>
  );
};

export default RoleOverviewPage;
