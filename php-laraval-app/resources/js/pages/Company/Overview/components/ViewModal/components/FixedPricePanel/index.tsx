import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";
import { AiOutlineEye } from "react-icons/ai";

import DataTable from "@/components/DataTable";
import { ModalExpansion } from "@/components/Modal/types";

import { usePageModal } from "@/hooks/modal";

import ViewModal from "@/pages/Company/FixedPrice/components/ViewModal";

import { useGetCompanyFixedPrices } from "@/services/companyFixedPrice";

import FixedPrice from "@/types/fixedPrice";
import { PagePagination } from "@/types/pagination";
import User from "@/types/user";

import { hasPermission } from "@/utils/authorization";
import { RequestQueryStringOptions } from "@/utils/request";

import { PageFilterItem } from "@/types";

import getColumns from "./column";
import CreateForm from "./components/CreateForm";
import DeleteModal from "./components/DeleteModal";
import RestoreModal from "./components/RestoreModal";

interface FixedPricePanelProps extends TabPanelProps {
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

const FixedPricePanel = ({
  data,
  onModalExpansion,
  onModalShrink,
  ...props
}: FixedPricePanelProps) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<
    FixedPrice,
    "view"
  >();

  const columns = useConst(getColumns(t, data.id));
  const [requestOptions, setRequestOptions] = useState<
    Partial<RequestQueryStringOptions<FixedPrice>>
  >({ filter: { eq: { isActive: true } } });

  const fixedPrices = useGetCompanyFixedPrices({
    request: {
      ...requestOptions,
      show: "all",
      only: [
        "id",
        "hasActiveSubscriptions",
        "type",
        "isPerOrder",
        "startDate",
        "endDate",
        "createdAt",
        "updatedAt",
        "isActive",
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
          title={t("fixed price")}
          size="md"
          data={fixedPrices.data?.data || []}
          columns={columns}
          fetchFn={(options) => setRequestOptions(options)}
          isFetching={fixedPrices.isFetching}
          filters={defaultFilters}
          pagination={fixedPrices.data?.pagination as PagePagination}
          sort={requestOptions.sort as Record<string, "asc" | "desc">}
          withCreate={hasPermission("company fixed prices create")}
          withDelete={(row) =>
            hasPermission("company fixed prices delete") &&
            !row.original.deletedAt
          }
          withRestore={(row) =>
            hasPermission("company fixed prices restore") &&
            !!row.original.deletedAt
          }
          onCreate={() =>
            onModalExpansion({
              title: t("create fixed price"),
              content: (
                <CreateForm
                  user={data}
                  onRefetch={fixedPrices.refetch}
                  onClose={onModalShrink}
                />
              ),
            })
          }
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
        />
      </TabPanel>

      <ViewModal
        fixedPriceId={modalData?.id}
        isOpen={modal === "view"}
        onRefetch={fixedPrices.refetch}
        onClose={closeModal}
      />
      <DeleteModal
        user={data}
        data={modalData}
        isOpen={modal === "delete"}
        onRefetch={fixedPrices.refetch}
        onClose={closeModal}
      />
      <RestoreModal
        user={data}
        data={modalData}
        isOpen={modal === "restore"}
        onRefetch={fixedPrices.refetch}
        onClose={closeModal}
      />
    </>
  );
};

export default FixedPricePanel;
