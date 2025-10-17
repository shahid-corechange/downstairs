import { Breadcrumb, Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { AiOutlineEye } from "react-icons/ai";

import BrandText from "@/components/BrandText";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { getPriceAdjustments } from "@/services/priceAdjustment";

import useAuthStore from "@/stores/auth";

import PriceAdjustment from "@/types/priceAdjustment";

import { hasPermission } from "@/utils/authorization";

import { PaginatedPageProps } from "@/types";

import getColumns from "./column";
import CreateModal from "./components/CreateModal";
import DeleteModal from "./components/DeleteModal";
import EditModal from "./components/EditModal";
import ViewModal from "./components/ViewModal";

type PriceAdjustmentProps = {
  priceAdjustments: PriceAdjustment[];
};

const PriceAdjustmentOverviewPage = ({
  priceAdjustments,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<PriceAdjustmentProps>) => {
  const { t } = useTranslation();

  const { currency, language } = useAuthStore.getState();

  const { modal, modalData, openModal, closeModal } = usePageModal<
    PriceAdjustment,
    "view"
  >();

  const columns = useConst(getColumns(t, currency, language));

  return (
    <>
      <Head>
        <title>{t("price adjustments")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("price adjustments")} />
        </Flex>

        <DataTable
          data={priceAdjustments}
          columns={columns}
          title={t("price adjustment")}
          sort={sort}
          filters={filter.filters}
          orFilters={filter.orFilters}
          pagination={pagination}
          fetchFn={getPriceAdjustments}
          withCreate={hasPermission("price adjustment create")}
          withEdit={(row) =>
            hasPermission("price adjustment update") &&
            !row.original.deletedAt &&
            row.original.status === "pending"
          }
          withDelete={(row) =>
            hasPermission("price adjustment delete") &&
            !row.original.deletedAt &&
            row.original.status === "pending"
          }
          onCreate={() => openModal("create")}
          onEdit={(row) => openModal("edit", row.original)}
          onDelete={(row) => openModal("delete", row.original)}
          actions={[
            {
              label: t("view"),
              icon: AiOutlineEye,
              isHidden: () => !hasPermission("price adjustment read"),
              onClick: (row) => {
                openModal("view", row.original);
              },
            },
          ]}
          serverSide
          useWindowScroll
        />
        <ViewModal
          data={modalData}
          isOpen={modal === "view"}
          onClose={closeModal}
        />
        <CreateModal isOpen={modal === "create"} onClose={closeModal} />
        <EditModal
          data={modalData}
          isOpen={modal === "edit"}
          onClose={closeModal}
        />
        <DeleteModal
          data={modalData}
          isOpen={modal === "delete"}
          onClose={closeModal}
        />
      </MainLayout>
    </>
  );
};

export default PriceAdjustmentOverviewPage;
