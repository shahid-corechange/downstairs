import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { getFeedbacks } from "@/services/feedback";

import Feedback from "@/types/feedback";

import { hasPermission } from "@/utils/authorization";

import { PageFilterItem, PaginatedPageProps } from "@/types";

import getColumns from "./column";
import DeleteModal from "./components/DeleteModal";
import RestoreModal from "./components/RestoreModal";

type FeedbackProps = {
  feedbacks: Feedback[];
};

const defaultFilters: PageFilterItem[] = [
  {
    key: "deletedAt",
    criteria: "eq",
    value: false,
  },
];

const FeedbackOverviewPage = ({
  feedbacks,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<FeedbackProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<Feedback>();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("feedbacks")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("feedbacks")} />
        </Flex>

        <DataTable
          data={feedbacks}
          columns={columns}
          title={t("feedbacks")}
          sort={sort}
          filters={[...defaultFilters, ...filter.filters]}
          orFilters={filter.orFilters}
          pagination={pagination}
          fetchFn={getFeedbacks}
          withDelete={(row) =>
            hasPermission("feedbacks delete") && !row.original.deletedAt
          }
          withRestore={hasPermission("feedbacks restore")}
          onDelete={(row) => openModal("delete", row.original)}
          onRestore={(row) => openModal("restore", row.original)}
          serverSide
          useWindowScroll
        />
      </MainLayout>
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

export default FeedbackOverviewPage;
