import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { AiOutlineFileText } from "react-icons/ai";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { getLaundryOrders } from "@/services/laundryOrder";

import { LaundryOrder } from "@/types/laundryOrder";

import { hasAnyPermissions } from "@/utils/authorization";

import { PaginatedPageProps } from "@/types";

import getColumns from "./column";
import ReceiptModal from "./components/ReceiptModal";

type LaundryOrderProps = {
  laundryOrders: LaundryOrder[];
};

const LaundryOrderOverviewPage = ({
  laundryOrders,
}: PaginatedPageProps<LaundryOrderProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<
    LaundryOrder,
    "receipt"
  >();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("laundry orders")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("laundry orders")} />
        </Flex>

        <DataTable
          data={laundryOrders}
          columns={columns}
          fetchFn={getLaundryOrders}
          actions={[
            {
              label: t("receipt"),
              icon: AiOutlineFileText,
              isHidden: !hasAnyPermissions(["laundry orders read"]),
              onClick: (row) => openModal("receipt", row.original),
            },
          ]}
          serverSide
          useWindowScroll
        />
      </MainLayout>

      <ReceiptModal
        laundryOrderId={modalData?.id}
        isOpen={modal === "receipt"}
        onClose={closeModal}
      />
    </>
  );
};

export default LaundryOrderOverviewPage;
