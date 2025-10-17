import {
  Badge,
  Button,
  Flex,
  Heading,
  Icon,
  Spacer,
  Text,
  Tooltip,
} from "@chakra-ui/react";
import { useTranslation } from "react-i18next";
import { AiOutlineFieldTime, AiOutlineIdcard } from "react-icons/ai";
import { HiOutlineMail } from "react-icons/hi";
import { LuPhone } from "react-icons/lu";
import { TbFileInvoice } from "react-icons/tb";

import Map from "@/components/Map";
import { ModalExpansion } from "@/components/Modal/types";

import Customer from "@/types/customer";
import CustomerDiscount from "@/types/customerDiscount";

import EditForm from "./components/EditForm";

interface CompanyCustomerFormProps {
  companyId: number;
  customer?: Customer;
  discount?: CustomerDiscount;
  onModalExpansion: (expansion: ModalExpansion) => void;
  onModalShrink: () => void;
  onRefetch: () => void;
}

const CompanyCustomerForm = ({
  companyId,
  customer,
  discount,
  onModalExpansion,
  onModalShrink,
  onRefetch,
}: CompanyCustomerFormProps) => {
  const { t } = useTranslation();

  return (
    <Flex direction="column" gap={4}>
      <Flex direction="column" justify="space-between" gap={3} mb={8}>
        <Flex align="center" gap={4}>
          <Heading size="md" color="brand.500" _dark={{ color: "brand.100" }}>
            {customer?.name}
          </Heading>
          <Spacer />
          {customer && (
            <Badge variant="subtle">{t(customer.membershipType)}</Badge>
          )}
        </Flex>
        <Heading
          size="sm"
          color="gray.600"
          fontWeight="medium"
          _dark={{ color: "gray.300" }}
        >
          {customer?.address?.fullAddress ?? ""}
        </Heading>
      </Flex>
      <Flex direction={{ base: "column", md: "row" }} gap={3} mb={8}>
        <Flex align="center" gap={4} flex={1}>
          <Tooltip label={t("organization number")}>
            <Flex align="center">
              <Icon as={AiOutlineIdcard} />
            </Flex>
          </Tooltip>
          <Text fontSize="sm" fontWeight="300">
            {customer?.identityNumber || "-"}
          </Text>
        </Flex>
        <Flex align="center" gap={4} flex={1}>
          <Tooltip label={t("phone")}>
            <Flex align="center">
              <Icon as={LuPhone} />
            </Flex>
          </Tooltip>
          <Text fontSize="sm" fontWeight="300">
            {customer?.phone1 || "-"}
          </Text>
        </Flex>

        <Flex align="center" gap={4} flex={1}>
          <Tooltip label={t("email")}>
            <Flex align="center">
              <Icon as={HiOutlineMail} />
            </Flex>
          </Tooltip>
          <Text fontSize="sm" fontWeight="300">
            {customer?.email || "-"}
          </Text>
        </Flex>
      </Flex>
      <Flex direction={{ base: "column", md: "row" }} gap={3} mb={8}>
        <Flex align="center" gap={4} flex={1}>
          <Tooltip label={t("invoice due days")}>
            <Flex align="center">
              <Icon as={AiOutlineFieldTime} />
            </Flex>
          </Tooltip>
          <Text fontSize="sm" fontWeight="300">
            {customer?.dueDays || "-"} {t("days")}
          </Text>
        </Flex>
        <Flex align="center" gap={4} flex={1}>
          <Tooltip label={t("send invoice method")}>
            <Flex align="center">
              <Icon as={TbFileInvoice} />
            </Flex>
          </Tooltip>
          <Text fontSize="sm" fontWeight="300">
            {customer ? t(customer.invoiceMethod) : ""}
          </Text>
        </Flex>
        <Flex align="center" gap={4} flex={1}></Flex>
      </Flex>
      {!!customer?.address?.latitude && !!customer?.address.longitude && (
        <Map
          height="300px"
          rounded="md"
          center={{
            lat: customer.address.latitude,
            lng: customer.address.longitude,
          }}
          markers={[
            {
              position: {
                lat: customer.address.latitude,
                lng: customer.address.longitude,
              },
              tooltipContainer: {
                direction: "top",
                offset: [0, -20],
                permanent: true,
              },
              tooltip: customer.address.address,
            },
          ]}
          mapContainer={{ zoomControl: false, attributionControl: false }}
        />
      )}
      <Flex align="center" mt={8}>
        <Spacer />
        <Button
          fontSize="sm"
          onClick={() =>
            customer
              ? onModalExpansion({
                  content: (
                    <EditForm
                      companyId={companyId}
                      customer={customer}
                      discount={discount}
                      onCancel={onModalShrink}
                      onRefetch={onRefetch}
                    />
                  ),
                  title: t("edit company address"),
                })
              : null
          }
        >
          {t("edit")}
        </Button>
      </Flex>
    </Flex>
  );
};

export default CompanyCustomerForm;
