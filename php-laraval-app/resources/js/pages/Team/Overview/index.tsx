import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import Team from "@/types/team";
import User from "@/types/user";

import { hasPermission } from "@/utils/authorization";

import { PageProps } from "@/types";

import getColumns from "./column";
import CreateModal from "./components/CreateModal";
import DeleteModal from "./components/DeleteModal";
import EditModal from "./components/EditModal";
import RestoreModal from "./components/RestoreModal";

type TeamProps = {
  teams: Team[];
  workers: User[];
};

const defaultFilters = [{ id: "isActive", value: "true" }];

const TeamOverviewPage = ({ teams, workers }: PageProps<TeamProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<Team>();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("teams")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("teams")} />
        </Flex>

        <DataTable
          data={teams}
          columns={columns}
          filters={defaultFilters}
          title={t("team")}
          withCreate={hasPermission("teams create")}
          withEdit={(row) =>
            hasPermission("teams update") && !row.original.deletedAt
          }
          withDelete={(row) =>
            hasPermission("teams delete") && !row.original.deletedAt
          }
          withRestore={hasPermission("teams restore")}
          onCreate={() => openModal("create")}
          onEdit={(row) => openModal("edit", row.original)}
          onDelete={(row) => openModal("delete", row.original)}
          onRestore={(row) => openModal("restore", row.original)}
          useWindowScroll
        />
      </MainLayout>
      <CreateModal
        workers={workers}
        isOpen={modal === "create"}
        onClose={closeModal}
      />
      <EditModal
        data={modalData}
        workers={workers}
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

export default TeamOverviewPage;
