import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { TbCalendarCancel, TbCalendarStats } from "react-icons/tb";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { getLeaveRegistrations } from "@/services/leaveRegistration";

import LeaveRegistration from "@/types/leaveRegistration";

import { hasPermission } from "@/utils/authorization";

import { PageFilterItem, PaginatedPageProps } from "@/types";

import getColumns from "./column";
import CreateModal from "./components/CreateModal";
import DeleteModal from "./components/DeleteModal";
import EditModal from "./components/EditModal";
import StopModal from "./components/StopModal";
import WorkerCollisionModal from "./components/WorkerCollisionModal";
import { LeaveRegistrationProps } from "./types";

const defaultFilters: PageFilterItem[] = [
  {
    key: "isStopped",
    criteria: "eq",
    value: "false",
  },
];

const LeaveRegistrationOverviewPage = ({
  leaveRegistrations,
  employees,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<LeaveRegistrationProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<
    LeaveRegistration,
    "stop" | "reschedule"
  >();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("leave registration")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("leave registration")} />
        </Flex>

        <DataTable
          data={leaveRegistrations}
          columns={columns}
          title={t("leave registration")}
          fetchFn={getLeaveRegistrations}
          sort={sort}
          filters={[...defaultFilters, ...filter.filters]}
          orFilters={filter.orFilters}
          pagination={pagination}
          withCreate={hasPermission("leave registrations create")}
          withEdit={(row) =>
            hasPermission("leave registrations update") &&
            !row.original.isStopped &&
            !row.original.deletedAt
          }
          withDelete={(row) =>
            hasPermission("leave registrations delete") &&
            !row.original.isStopped &&
            !row.original.deletedAt
          }
          onCreate={() => openModal("create")}
          onEdit={(row) => openModal("edit", row.original)}
          onDelete={(row) => openModal("delete", row.original)}
          actions={[
            {
              label: t("stop"),
              icon: TbCalendarCancel,
              colorScheme: "red",
              color: "red.500",
              _dark: { color: "red.200" },
              isHidden: (row) =>
                !hasPermission("leave registrations create") ||
                row.original.isStopped ||
                !!row.original.deletedAt ||
                row.original.isPaused,
              onClick: (row) => {
                openModal("stop", row.original);
              },
            },
            {
              label: t("reschedule"),
              icon: TbCalendarStats,
              isHidden: (row) =>
                !hasPermission("leave registrations create") ||
                row.original.isStopped ||
                !!row.original.deletedAt ||
                !row.original.rescheduleNeeded,
              onClick: (row) => {
                openModal("reschedule", row.original);
              },
            },
          ]}
          serverSide
          useWindowScroll
        />
      </MainLayout>
      <CreateModal
        employees={employees}
        isOpen={modal === "create"}
        onClose={closeModal}
      />
      <EditModal
        data={modalData}
        isOpen={modal === "edit"}
        onClose={closeModal}
      />
      <StopModal
        data={modalData}
        isOpen={modal === "stop"}
        onClose={closeModal}
      />
      <DeleteModal
        data={modalData}
        isOpen={modal === "delete"}
        onClose={closeModal}
      />
      <WorkerCollisionModal
        leaveRegistrationId={modalData?.id || 0}
        isOpen={modal === "reschedule"}
        onClose={closeModal}
      />
    </>
  );
};

export default LeaveRegistrationOverviewPage;
