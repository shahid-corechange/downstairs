import { TabPanel, TabPanelProps, useConst } from "@chakra-ui/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import DeleteModal from "@/pages/CompanyProperty/Overview/components/DeleteModal";
import EditModal from "@/pages/CompanyProperty/Overview/components/EditModal";
import RestoreModal from "@/pages/CompanyProperty/Overview/components/RestoreModal";

import { useGetCustomerProperties } from "@/services/customer";

import Property from "@/types/property";

import { hasPermission } from "@/utils/authorization";
import { RequestQueryStringOptions } from "@/utils/request";

import { PageFilterItem } from "@/types";

import getColumns from "./columns";

interface PropertyPanelProps extends TabPanelProps {
  userId: number;
}

const defaultFilters: PageFilterItem[] = [
  {
    key: "status",
    criteria: "eq",
    value: "active",
  },
];

const PropertyPanel = ({ userId, ...props }: PropertyPanelProps) => {
  const { t } = useTranslation();

  const columns = useConst(getColumns(t, String(userId)));
  const { modal, modalData, openModal, closeModal } = usePageModal<Property>();
  const [requestOptions, setRequestOptions] = useState<
    Partial<RequestQueryStringOptions<Property>>
  >({
    filter: {
      eq: {
        membershipType: "company",
      },
    },
  });

  const customerProperties = useGetCustomerProperties(userId, {
    request: {
      ...requestOptions,
      size: -1,
      include: [
        "address",
        "address.city.country",
        "keyInformation",
        "keyInformation.keyPlace",
        "keyInformation.frontDoorCode",
        "keyInformation.alarmCodeOff",
        "keyInformation.alarmCodeOn",
        "keyInformation.information",
        "type",
        "meta",
      ],
      only: [
        "id",
        "address.fullAddress",
        "address.id",
        "address.cityId",
        "address.address",
        "address.postalCode",
        "address.latitude",
        "address.longitude",
        "address.city.name",
        "address.city.countryId",
        "address.city.country.name",
        "status",
        "squareMeter",
        "keyDescription",
        "keyInformation",
        "keyInformation.keyPlace",
        "keyInformation.frontDoorCode",
        "keyInformation.alarmCodeOff",
        "keyInformation.alarmCodeOn",
        "keyInformation.information",
        "type.id",
        "type.name",
        "meta.note",
      ],
      pagination: "page",
    },
  });

  return (
    <>
      <TabPanel {...props}>
        <DataTable
          title={t("properties")}
          size="md"
          data={customerProperties.data || []}
          columns={columns}
          fetchFn={(options) => setRequestOptions(options)}
          isFetching={customerProperties.isFetching}
          filters={[...defaultFilters]}
          sort={requestOptions.sort as Record<string, "asc" | "desc">}
          withEdit={(row) =>
            hasPermission("properties update") && !row.original.deletedAt
          }
          withDelete={(row) =>
            hasPermission("properties delete") && !row.original.deletedAt
          }
          withRestore={hasPermission("properties restore")}
          onEdit={(row) => openModal("edit", row.original)}
          onDelete={(row) => openModal("delete", row.original)}
          onRestore={(row) => openModal("restore", row.original)}
          serverSide
          useWindowScroll
        />
      </TabPanel>
      <EditModal
        data={modalData}
        isOpen={modal === "edit"}
        onClose={closeModal}
        onSuccess={() => customerProperties.refetch()}
      />
      <DeleteModal
        data={modalData}
        isOpen={modal === "delete"}
        onClose={closeModal}
        onSuccess={() => customerProperties.refetch()}
      />
      <RestoreModal
        data={modalData}
        isOpen={modal === "restore"}
        onClose={closeModal}
        onSuccess={() => customerProperties.refetch()}
      />
    </>
  );
};

export default PropertyPanel;
