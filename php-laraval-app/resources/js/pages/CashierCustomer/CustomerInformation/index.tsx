import { Flex, Spinner } from "@chakra-ui/react";
import { useState } from "react";

import Modal from "@/components/Modal";
import { ModalExpansion } from "@/components/Modal/types";

import { ServiceMembershipType } from "@/constants/service";

import { useGetCashierDiscounts } from "@/services/cashierDiscount";
import { useGetCashierCustomerAddresses } from "@/services/customer";

import CompanyCustomerForm from "./components/CompanyCustomerModal";
import PrivateCustomerForm from "./components/PrivateCustomerModal";

interface CustomerInformationModalProps {
  userId: number;
  isOpen: boolean;
  onClose: () => void;
}

const CustomerInformationModal = ({
  userId,
  isOpen,
  onClose,
}: CustomerInformationModalProps) => {
  const [isExpanded, setIsExpanded] = useState(false);
  const [expandableContent, setExpandableContent] = useState<React.ReactNode>();
  const [expandableTitle, setExpandableTitle] = useState<string>();

  const {
    data: customer,
    isFetching,
    refetch,
  } = useGetCashierCustomerAddresses(userId, {
    request: {
      include: ["address.city.country", "users.info", "companyUser.info"],
      only: [
        "id",
        "customerRefId",
        "reference",
        "identityNumber",
        "phone1",
        "formattedPhone1",
        "email",
        "type",
        "name",
        "membershipType",
        "dueDays",
        "invoiceMethod",
        "deletedAt",
        "isFull",
        "address.address",
        "address.address2",
        "address.fullAddress",
        "address.postalCode",
        "address.latitude",
        "address.longitude",
        "address.cityId",
        "address.city.name",
        "address.city.countryId",
        "address.city.country.name",
        "users.firstName",
        "users.lastName",
        "users.fullname",
        "users.identityNumber",
        "users.email",
        "users.formattedCellphone",
        "users.status",
        "users.info.timezone",
        "users.info.language",
        "users.info.notificationMethod",
        "companyUser.firstName",
        "companyUser.email",
        "companyUser.formattedCellphone",
        "companyUser.info.notificationMethod",
      ],
      filter: {
        eq: {
          type: "primary",
        },
      },
      size: 1,
    },
    query: {
      enabled: !!userId && isOpen,
    },
  });

  const { data: discounts, refetch: refetchDiscounts } = useGetCashierDiscounts(
    {
      request: {
        filter: {
          eq: {
            userId: userId,
            isActive: true,
          },
        },
        only: ["id", "value", "startDate", "endDate"],
        sort: { value: "desc" },
        size: 1,
      },
      query: {
        enabled: !!userId && isOpen,
      },
    },
  );

  const handleModalExpansion = (expansion: ModalExpansion) => {
    setIsExpanded(true);
    setExpandableContent(expansion.content);
    setExpandableTitle(expansion.title);
  };

  const handleShrink = () => {
    setIsExpanded(false);
    setExpandableContent(undefined);
    setExpandableTitle(undefined);
  };

  const handleClose = () => {
    handleShrink();
    onClose();
  };

  const handleRefetch = () => {
    refetch();
    refetchDiscounts();
  };

  return (
    <Modal
      bodyContainer={{ p: 8 }}
      expandableContent={expandableContent}
      expandableTitle={expandableTitle}
      isExpanded={isExpanded}
      isOpen={isOpen}
      onClose={handleClose}
      onShrink={handleShrink}
      size="4xl"
    >
      {userId && !isFetching && customer ? (
        customer[0]?.membershipType === ServiceMembershipType.PRIVATE ? (
          <PrivateCustomerForm
            userId={userId}
            customer={customer[0]}
            discount={discounts?.data?.[0]}
            onModalExpansion={handleModalExpansion}
            onModalShrink={handleShrink}
            onRefetch={handleRefetch}
          />
        ) : (
          <CompanyCustomerForm
            companyId={userId}
            customer={customer[0]}
            discount={discounts?.data?.[0]}
            onModalExpansion={handleModalExpansion}
            onModalShrink={handleShrink}
            onRefetch={handleRefetch}
          />
        )
      ) : (
        <Flex h="xs" alignItems="center" justifyContent="center">
          <Spinner size="md" />
        </Flex>
      )}
    </Modal>
  );
};

export default CustomerInformationModal;
