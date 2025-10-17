import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { AiOutlineEye } from "react-icons/ai";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { getDeviations } from "@/services/deviation";

import ScheduleDeviation from "@/types/scheduleDeviation";

import { hasPermission } from "@/utils/authorization";

import { PaginatedPageProps } from "@/types";

import getColumns from "./column";
import ViewModal from "./components/ViewModal";

type DeviationProps = {
  deviations: ScheduleDeviation[];
};

const DeviationsOverviewPage = ({
  deviations,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<DeviationProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<
    ScheduleDeviation,
    "view"
  >();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("deviations")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("deviations")} />
        </Flex>

        <DataTable
          data={deviations}
          columns={columns}
          title={t("deviations")}
          fetchFn={getDeviations}
          sort={sort}
          filters={filter.filters}
          orFilters={filter.orFilters}
          pagination={pagination}
          actions={[
            {
              label: t("view"),
              icon: AiOutlineEye,
              isHidden: !hasPermission("deviations read"),
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
        deviationId={modalData?.id}
        isOpen={modal === "view"}
        onClose={closeModal}
      />
    </>
  );
};

export default DeviationsOverviewPage;
