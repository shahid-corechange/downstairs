import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { RiExternalLinkLine } from "react-icons/ri";

import Alert from "@/components/Alert";
import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { ServiceMembershipType } from "@/constants/service";

import MainLayout from "@/layouts/Main";

import { getKeyPlaces } from "@/services/keyplace";

import KeyPlace from "@/types/keyplace";

import { hasPermission } from "@/utils/authorization";

import { PaginatedPageProps } from "@/types";

import getColumns from "./column";

type KeyPlaceProps = {
  keyPlaces: KeyPlace[];
};

const KeyPlaceOverviewPage = ({
  keyPlaces,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<KeyPlaceProps>) => {
  const { t } = useTranslation();
  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("key places")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("key places")} />
        </Flex>
        <Alert
          status="info"
          title={t("info")}
          message={t("key place overview info")}
          fontSize="small"
          mb={6}
        />
        <DataTable
          data={keyPlaces}
          columns={columns}
          title={t("key places")}
          fetchFn={getKeyPlaces}
          sort={sort}
          filters={filter.filters}
          orFilters={filter.orFilters}
          pagination={pagination}
          serverSide
          useWindowScroll
          actions={[
            {
              label: t("property"),
              icon: RiExternalLinkLine,
              isHidden: (row) =>
                !hasPermission("properties index") || !row.original.propertyId,
              onClick: (row) => {
                const url =
                  row.original.property?.membershipType ===
                  ServiceMembershipType.PRIVATE
                    ? "/customers/properties"
                    : "/companies/properties";
                window.open(
                  `${url}?id.eq=${row.original.propertyId}`,
                  "_blank",
                );
              },
            },
          ]}
        />
      </MainLayout>
    </>
  );
};

export default KeyPlaceOverviewPage;
