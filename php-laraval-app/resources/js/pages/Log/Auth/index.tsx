import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import MainLayout from "@/layouts/Main";

import { getAuthLogs } from "@/services/log";

import AuthenticationLog from "@/types/authenticationLog";

import { PaginatedPageProps } from "@/types";

import getColumns from "./column";

type AuthLogProps = {
  authentications: AuthenticationLog[];
};

const AuthLogOverviewPage = ({
  authentications,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<AuthLogProps>) => {
  const { t } = useTranslation();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("authentication logs")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("authentication logs")} />
        </Flex>

        <DataTable
          data={authentications}
          columns={columns}
          title={t("authentication logs")}
          fetchFn={getAuthLogs}
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

export default AuthLogOverviewPage;
