import { useConst } from "@chakra-ui/react";
import { useTranslation } from "react-i18next";
import { RiExternalLinkLine } from "react-icons/ri";

import DataTable from "@/components/DataTable";
import Modal from "@/components/Modal";

import { ServiceMembershipType } from "@/constants/service";

import Customer from "@/types/customer";

import getColumns from "./column";

export interface CustomerAddressReferenceModalProps {
  isOpen: boolean;
  onClose: () => void;
  data: Customer[];
}

const CustomerAddressReferenceModal = ({
  data,
  onClose,
  isOpen,
}: CustomerAddressReferenceModalProps) => {
  const { t } = useTranslation();
  const columns = useConst(getColumns(t));

  return (
    <Modal title={t("references")} onClose={onClose} isOpen={isOpen}>
      <DataTable
        data={data}
        columns={columns}
        actions={[
          {
            label: t("view"),
            icon: RiExternalLinkLine,
            onClick: (row) => {
              const { membershipType, companyUser, users } = row.original;

              if (membershipType === ServiceMembershipType.COMPANY) {
                return window.open(
                  `/companies?companyUser.id=${companyUser?.id}`,
                  "_blank",
                );
              }

              const userIds = (users ?? []).map(({ id }) => id).join(",");
              return window.open(`/customers?id.in=${userIds}`, "_blank");
            },
          },
        ]}
      />
    </Modal>
  );
};

export default CustomerAddressReferenceModal;
