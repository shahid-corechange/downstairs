import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { AiOutlineFileText } from "react-icons/ai";

import BrandText from "@/components/BrandText";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import CashierLayout from "@/layouts/Cashier";

import { getStoreSales } from "@/services/storeSale";

import StoreSale from "@/types/storeSale";

import { PaginatedPageProps } from "@/types";

import getColumns from "./column";
import ReceiptModal from "./components/ReceiptModal";

type DirectSaleHistoryProps = {
  storeSales: StoreSale[];
};

const DirectSaleHistoryPage = ({
  storeSales,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<DirectSaleHistoryProps>) => {
  const { t } = useTranslation();

  const columns = useConst(getColumns(t));

  const { modalData, modal, openModal, closeModal } = usePageModal<
    StoreSale,
    "receipt"
  >();

  return (
    <>
      <Head>
        <title>{t("shopping histories")}</title>
      </Head>
      <CashierLayout content={{ p: 4 }}>
        <Flex direction="column">
          <BrandText text={t("shopping histories")} />
        </Flex>

        <DataTable
          title={t("shopping histories")}
          data={storeSales}
          columns={columns}
          fetchFn={getStoreSales}
          isFetching={false}
          pagination={pagination}
          sort={sort}
          filters={filter.filters}
          orFilters={filter.orFilters}
          useWindowScroll
          serverSide
          actions={[
            {
              label: t("receipt"),
              icon: AiOutlineFileText,
              onClick: (row) => openModal("receipt", row.original),
            },
          ]}
        />
      </CashierLayout>

      <ReceiptModal
        storeSaleId={modalData?.id}
        isOpen={modal === "receipt" && !!modalData}
        onClose={closeModal}
      />
    </>
  );
};

export default DirectSaleHistoryPage;
