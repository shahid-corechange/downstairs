import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { AiOutlineEye } from "react-icons/ai";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { getFixedPrices } from "@/services/fixedPrice";

import FixedPrice from "@/types/fixedPrice";

import { hasPermission } from "@/utils/authorization";

import { PageFilterItem, PaginatedPageProps } from "@/types";

import getColumns from "./column";
import CreateModal from "./components/CreateModal";
import DeleteModal from "./components/DeleteModal";
import RestoreModal from "./components/RestoreModal";
import ViewModal from "./components/ViewModal";
import { FixedPriceProps } from "./types";

const defaultFilters: PageFilterItem[] = [
  {
    key: "isActive",
    criteria: "eq",
    value: true,
  },
];

const FixedPriceOverviewPage = ({
  fixedPrices,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<FixedPriceProps>) => {
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
          fetchFn={getFixedPrices}
          withCreate={hasPermission("fixed prices create")}
          withDelete={(row) =>
            hasPermission("fixed prices delete") && !row.original.deletedAt
          }
          withRestore={hasPermission("fixed prices restore")}
          onCreate={() => openModal("create")}
          onDelete={(row) => openModal("delete", row.original)}
          onRestore={(row) => openModal("restore", row.original)}
          actions={[
            {
              label: t("view"),
              icon: AiOutlineEye,
              isHidden: (row) =>
                !hasPermission("fixed prices read") || !!row.original.deletedAt,
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

export default FixedPriceOverviewPage;
