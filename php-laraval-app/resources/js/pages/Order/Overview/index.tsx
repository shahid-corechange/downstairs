import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";
import { AiOutlineEye } from "react-icons/ai";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import MainLayout from "@/layouts/Main";

import { getOrders } from "@/services/order";

import Order from "@/types/order";

import { hasAnyPermissions } from "@/utils/authorization";

import { PaginatedPageProps } from "@/types";

import getColumns from "./column";
import ViewModal from "./components/ViewModal";

type OrderProps = {
  orders: Order[];
  extraArticleIds: number[];
};

const OrderOverviewPage = ({
  orders,
  sort,
  filter,
  pagination,
  extraArticleIds,
}: PaginatedPageProps<OrderProps>) => {
  const { t } = useTranslation();

  const [selectedOrderIndex, setSelectedOrderIndex] = useState<number>();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("orders")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("orders")} />
        </Flex>

        <DataTable
          data={orders}
          columns={columns}
          fetchFn={getOrders}
          sort={sort}
          filters={filter.filters}
          orFilters={filter.orFilters}
          pagination={pagination}
          actions={[
            {
              label: t("view"),
              icon: AiOutlineEye,
              isHidden: !hasAnyPermissions(["orders read", "order rows index"]),
              onClick: (row) => setSelectedOrderIndex(row.index),
            },
          ]}
          serverSide
          useWindowScroll
        />
      </MainLayout>

      <ViewModal
        isOpen={selectedOrderIndex !== undefined}
        onClose={() => setSelectedOrderIndex(undefined)}
        orderId={
          selectedOrderIndex !== undefined
            ? orders[selectedOrderIndex].id
            : undefined
        }
        extraArticleIds={extraArticleIds}
      />
    </>
  );
};

export default OrderOverviewPage;
