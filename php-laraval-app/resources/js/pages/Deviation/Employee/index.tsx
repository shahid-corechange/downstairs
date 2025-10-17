import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import {
  AiOutlineCalendar,
  AiOutlineCheckCircle,
  AiOutlineUndo,
} from "react-icons/ai";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { getEmployeesDeviations } from "@/services/deviation";

import Deviation from "@/types/deviation";

import { hasPermission } from "@/utils/authorization";

import { PaginatedPageProps } from "@/types";

import getColumns from "./column";
import EditAttendanceModal from "./components/EditAttendanceModal";
import HandleModal from "./components/HandleModal";
import RevertModal from "./components/RevertModal";

type EmployeeDeviationProps = {
  deviations: Deviation[];
};

const EmployeeDeviationsOverviewPage = ({
  deviations,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<EmployeeDeviationProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<
    Deviation,
    "handle" | "editAttendance" | "revert"
  >();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("employees deviations")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("employees deviations")} />
        </Flex>

        <DataTable
          data={deviations}
          columns={columns}
          title={t("employees deviations")}
          fetchFn={getEmployeesDeviations}
          sort={sort}
          filters={filter.filters}
          orFilters={filter.orFilters}
          pagination={pagination}
          actions={[
            (row) =>
              !row.original.isHandled
                ? {
                    label: t("handle"),
                    colorScheme: "green",
                    color: "green.500",
                    _dark: {
                      color: "green.200",
                    },
                    icon: AiOutlineCheckCircle,
                    isHidden: !hasPermission("deviations handle"),
                    onClick: (row) => {
                      openModal("handle", row.original);
                    },
                  }
                : null,
            (row) =>
              !row.original.isHandled &&
              [
                "start wrong time",
                "stop wrong time",
                "not started",
                "finished early",
              ].includes(row.original.type)
                ? {
                    label: t("edit attendance"),
                    icon: AiOutlineCalendar,
                    onClick: (row) => {
                      openModal("editAttendance", row.original);
                    },
                  }
                : null,
            (row) =>
              !row.original.isHandled &&
              row.original.type === "canceled" &&
              ["booked", "progress"].includes(
                row.original.schedule?.status ?? "",
              )
                ? {
                    label: t("revert"),
                    icon: AiOutlineUndo,
                    isHidden: !hasPermission("deviations handle"),
                    onClick: (row) => {
                      openModal("revert", row.original);
                    },
                  }
                : null,
          ]}
          serverSide
          useWindowScroll
        />
      </MainLayout>

      <RevertModal
        deviation={modalData!}
        isOpen={!!modalData && modal === "revert"}
        onClose={() => {
          closeModal();
          getEmployeesDeviations({});
        }}
      />

      <HandleModal
        data={modalData}
        isOpen={modal === "handle"}
        onClose={closeModal}
      />

      <EditAttendanceModal
        data={modalData}
        isOpen={modal === "editAttendance"}
        onClose={closeModal}
      />
    </>
  );
};

export default EmployeeDeviationsOverviewPage;
