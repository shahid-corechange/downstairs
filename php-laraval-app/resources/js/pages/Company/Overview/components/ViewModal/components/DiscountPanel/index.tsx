import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import DataTable from "@/components/DataTable";
import { ModalExpansion } from "@/components/Modal/types";

import { usePageModal } from "@/hooks/modal";

import { useGetCompanyDiscounts } from "@/services/companyDiscount";

import CustomerDiscount from "@/types/customerDiscount";
import { PagePagination } from "@/types/pagination";
import User from "@/types/user";

import { hasPermission } from "@/utils/authorization";
import { RequestQueryStringOptions } from "@/utils/request";

import { PageFilterItem } from "@/types";

import getColumns from "./column";
import CreateForm from "./components/CreateForm";
import DeleteModal from "./components/DeleteModal";
import EditForm from "./components/EditForm";
import RestoreModal from "./components/RestoreModal";

interface DiscountPanelProps extends TabPanelProps {
  data: User;
  onModalExpansion: (expansion: ModalExpansion) => void;
  onModalShrink: () => void;
}

const defaultFilters: PageFilterItem[] = [
  {
    key: "isActive",
    criteria: "eq",
    value: true,
  },
];

const DiscountPanel = ({
  data,
  onModalExpansion,
  onModalShrink,
  ...props
}: DiscountPanelProps) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } =
    usePageModal<CustomerDiscount>();

  const columns = useConst(getColumns(t, data.id));
  const [requestOptions, setRequestOptions] = useState<
    Partial<RequestQueryStringOptions<CustomerDiscount>>
  >({ filter: { eq: { isActive: true } } });

  const discounts = useGetCompanyDiscounts({
    request: {
      ...requestOptions,
      show: "all",
      only: [
        "id",
        "isActive",
        "type",
        "value",
        "usageLimit",
        "startDate",
        "endDate",
        "deletedAt",
      ],
      filter: {
        ...requestOptions.filter,
        eq: {
          ...requestOptions.filter?.eq,
          userId: data.id,
        },
      },
      pagination: "page",
    },
  });

  return (
    <>
      <TabPanel {...props}>
        <DataTable
          title={t("discount")}
          size="md"
          data={discounts.data?.data || []}
          columns={columns}
          fetchFn={(options) => setRequestOptions(options)}
          isFetching={discounts.isFetching}
          filters={defaultFilters}
          pagination={discounts.data?.pagination as PagePagination}
          sort={requestOptions.sort as Record<string, "asc" | "desc">}
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
          onCreate={() =>
            onModalExpansion({
              title: t("create customer discount"),
              content: (
                <CreateForm
                  user={data}
                  onRefetch={discounts.refetch}
                  onClose={onModalShrink}
                />
              ),
            })
          }
          onEdit={(row) =>
            onModalExpansion({
              title: t("edit customer discount"),
              content: (
                <EditForm
                  user={data}
                  data={row.original}
                  onRefetch={discounts.refetch}
                  onClose={onModalShrink}
                />
              ),
            })
          }
          onDelete={(row) => openModal("delete", row.original)}
          onRestore={(row) => openModal("restore", row.original)}
          serverSide
        />
      </TabPanel>

      <DeleteModal
        user={data}
        data={modalData}
        isOpen={modal === "delete"}
        onRefetch={discounts.refetch}
        onClose={closeModal}
      />
      <RestoreModal
        user={data}
        data={modalData}
        isOpen={modal === "restore"}
        onRefetch={discounts.refetch}
        onClose={closeModal}
      />
    </>
  );
};

export default DiscountPanel;
