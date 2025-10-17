import { Flex, useConst } from "@chakra-ui/react";
import { Head, router } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import CustomerDiscount, { DiscountType } from "@/types/customerDiscount";

import { hasPermission } from "@/utils/authorization";
import { RequestQueryStringOptions, createQueryString } from "@/utils/request";

import { PageFilterItem, PaginatedPageProps } from "@/types";

import getColumns from "./column";
import CreateModal from "./components/CreateModal";
import DeleteModal from "./components/DeleteModal";
import EditModal from "./components/EditModal";
import RestoreModal from "./components/RestoreModal";

type CustomerDiscountProps = {
  customerDiscounts: CustomerDiscount[];
  customerDiscountTypes: DiscountType;
};

const defaultFilters: PageFilterItem[] = [
  {
    key: "isActive",
    criteria: "eq",
    value: true,
  },
];

const getCustomerDiscount = (
  options: Partial<RequestQueryStringOptions<CustomerDiscount>>,
) => {
  router.get("/customers/discounts" + createQueryString(options), undefined, {
    preserveState: true,
  });
};

const DiscountOverviewPage = ({
  customerDiscounts,
  customerDiscountTypes,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<CustomerDiscountProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<
    CustomerDiscount,
    "view"
  >();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("discounts")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("discounts")} />
        </Flex>

        <DataTable
          data={customerDiscounts}
          columns={columns}
          title={t("discount")}
          sort={sort}
          filters={[...defaultFilters, ...filter.filters]}
          orFilters={filter.orFilters}
          pagination={pagination}
          fetchFn={getCustomerDiscount}
          withCreate={hasPermission("customer discounts create")}
          withEdit={(row) =>
            hasPermission("customer discounts update") &&
            !row.original.deletedAt
          }
          withDelete={(row) =>
            hasPermission("customer discounts delete") &&
            !row.original.deletedAt
          }
          withRestore={(row) =>
            hasPermission("customer discounts restore") &&
            !!row.original.deletedAt
          }
          onCreate={() => openModal("create")}
          onEdit={(row) => openModal("edit", row.original)}
          onDelete={(row) => openModal("delete", row.original)}
          onRestore={(row) => openModal("restore", row.original)}
          serverSide
          useWindowScroll
        />
      </MainLayout>
      <CreateModal
        discountType={customerDiscountTypes}
        isOpen={modal === "create"}
        onClose={closeModal}
      />
      <EditModal
        data={modalData}
        discountType={customerDiscountTypes}
        isOpen={modal === "edit"}
        onClose={closeModal}
      />
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

export default DiscountOverviewPage;
