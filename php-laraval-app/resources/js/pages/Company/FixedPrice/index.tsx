import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { AiOutlineEye } from "react-icons/ai";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { getCompanyFixedPrices } from "@/services/companyFixedPrice";

import FixedPrice from "@/types/fixedPrice";

import { hasPermission } from "@/utils/authorization";

import { PageFilterItem, PaginatedPageProps } from "@/types";

import getColumns from "./column";
import CreateModal from "./components/CreateModal";
import DeleteModal from "./components/DeleteModal";
import RestoreModal from "./components/RestoreModal";
import ViewModal from "./components/ViewModal";
import { CompanyFixedPriceProps } from "./types";

const defaultFilters: PageFilterItem[] = [
  {
    key: "isActive",
    criteria: "eq",
    value: true,
  },
];

const CompanyFixedPriceOverviewPage = ({
  fixedPrices,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<CompanyFixedPriceProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<
    FixedPrice,
    "view"
  >();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("fixed prices")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("fixed prices")} />
        </Flex>

        <DataTable
          data={fixedPrices}
          columns={columns}
          title={t("fixed prices")}
          sort={sort}
          filters={[...defaultFilters, ...filter.filters]}
          orFilters={filter.orFilters}
          pagination={pagination}
          fetchFn={getCompanyFixedPrices}
          withCreate={hasPermission("company fixed prices create")}
          withDelete={(row) =>
            hasPermission("company fixed prices delete") &&
            !row.original.deletedAt
          }
          withRestore={hasPermission("company fixed prices restore")}
          onCreate={() => openModal("create")}
          onDelete={(row) => openModal("delete", row.original)}
          onRestore={(row) => openModal("restore", row.original)}
          actions={[
            {
              label: t("view"),
              icon: AiOutlineEye,
              isHidden: (row) =>
                !hasPermission("company fixed prices read") ||
                !!row.original.deletedAt,
              onClick: (row) => openModal("view", row.original),
            },
          ]}
          serverSide
          useWindowScroll
        />
      </MainLayout>
      <ViewModal
        fixedPriceId={modalData?.id}
        isOpen={modal === "view"}
        onClose={closeModal}
      />
      <CreateModal isOpen={modal === "create"} onClose={closeModal} />
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

export default CompanyFixedPriceOverviewPage;
