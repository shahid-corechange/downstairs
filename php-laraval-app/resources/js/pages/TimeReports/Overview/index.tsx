import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { RiExternalLinkLine } from "react-icons/ri";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { DATE_FORMAT } from "@/constants/datetime";

import MainLayout from "@/layouts/Main";

import { getMonthlyTimeReports } from "@/services/timeReports";

import { hasAnyPermissions } from "@/utils/authorization";
import { toDayjs } from "@/utils/datetime";

import { PaginatedPageProps } from "@/types";

import getColumns from "./column";
import { MonthlyTimeReportProps } from "./types";

const TimeReportsOverviewPage = ({
  monthlyTimeReports,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<MonthlyTimeReportProps>) => {
  const { t } = useTranslation();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("time reports")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("time reports")} />
        </Flex>

        <DataTable
          data={monthlyTimeReports}
          columns={columns}
          sort={sort}
          filters={filter.filters}
          orFilters={filter.orFilters}
          pagination={pagination}
          fetchFn={getMonthlyTimeReports}
          actions={[
            {
              label: t("view"),
              icon: RiExternalLinkLine,
              isHidden: () => !hasAnyPermissions(["time reports index"]),
              onClick: (row) => {
                const id = row.original.userId;
                const month = row.original.month.toString().padStart(2, "0");
                const year = row.original.year;
                const startDate = `${year}-${month}-01`;
                const endDate = toDayjs(`${year}-${month}-01`, false)
                  .endOf("month")
                  .format(DATE_FORMAT);

                window.open(
                  `/time-reports/daily?user.id.eq=${id}&date.between=${startDate},${endDate}`,
                  "_blank",
                );
              },
            },
          ]}
          serverSide
          useWindowScroll
        />
      </MainLayout>
    </>
  );
};

export default TimeReportsOverviewPage;
