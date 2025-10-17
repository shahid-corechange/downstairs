import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { AiOutlineEye } from "react-icons/ai";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { getTimeReports } from "@/services/timeReports";

import TimeReport from "@/types/timeReport";

import { hasAnyPermissions } from "@/utils/authorization";

import { PaginatedPageProps } from "@/types";

import getColumns from "./column";
import ViewModal from "./components/ViewModal";
import { TimeReportProps } from "./types";

const TimeReportsOverviewPage = ({
  timeReports,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<TimeReportProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<
    TimeReport,
    "view"
  >();
  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("daily time reports")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("daily time reports")} />
        </Flex>

        <DataTable
          data={timeReports}
          columns={columns}
          sort={sort}
          filters={filter.filters}
          orFilters={filter.orFilters}
          pagination={pagination}
          fetchFn={getTimeReports}
          actions={[
            {
              label: t("view"),
              icon: AiOutlineEye,
              isHidden: () => !hasAnyPermissions(["time reports index"]),
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
        workHourId={modalData?.id}
        userId={modalData?.user?.id}
        date={modalData?.date}
        isOpen={modal === "view"}
        onClose={closeModal}
      />
    </>
  );
};

export default TimeReportsOverviewPage;
