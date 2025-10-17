import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import MainLayout from "@/layouts/Main";

import { getActivityLogs } from "@/services/log";

import ActivityLog from "@/types/activityLog";

import { PaginatedPageProps } from "@/types";

import getColumns from "./column";

type ActivityLogProps = {
  activities: ActivityLog[];
};

const ActivityLogOverviewPage = ({
  activities,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<ActivityLogProps>) => {
  const { t } = useTranslation();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("activity logs")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("activity logs")} />
        </Flex>

        <DataTable
          data={activities}
          columns={columns}
          title={t("activity logs")}
          fetchFn={getActivityLogs}
          sort={sort}
          filters={filter.filters}
          orFilters={filter.orFilters}
          pagination={pagination}
          serverSide
          useWindowScroll
        />
      </MainLayout>
    </>
  );
};

export default ActivityLogOverviewPage;
