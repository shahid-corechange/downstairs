import {
  Badge,
  Flex,
  Heading,
  Icon,
  Spacer,
  Text,
  Tooltip,
} from "@chakra-ui/react";
import { useTranslation } from "react-i18next";
import { IoPricetagOutline } from "react-icons/io5";
import {
  MdOutlineDateRange,
  MdOutlineTextFields,
  MdOutlineUpdate,
} from "react-icons/md";

import DataTable from "@/components/DataTable";
import Modal from "@/components/Modal";

import { DATETIME_FORMAT, DATE_FORMAT } from "@/constants/datetime";

import useAuthStore from "@/stores/auth";

import PriceAdjustment from "@/types/priceAdjustment";

import { formatCurrency } from "@/utils/currency";
import { toDayjs } from "@/utils/datetime";
import { getPriceTypeTranslation } from "@/utils/translation";

import getColumns from "./columns";

interface ViewModalProps {
  data?: PriceAdjustment;
  isOpen: boolean;
  onClose: () => void;
}

const BADGE_TYPE_COLORS: Record<string, string> = {
  fixed_price_with_vat: "green",
  dynamic_percentage: "yellow",
  dynamic_fixed_with_vat: "blue",
};

const ViewModal = ({ data, onClose, isOpen }: ViewModalProps) => {
  const { t } = useTranslation();
  const language = useAuthStore((state) => state.language);
  const currency = useAuthStore((state) => state.currency);
  const columns = getColumns(t);

  const adjustmentType = data?.type.replace("_", " ") || "";

  return (
    <Modal bodyContainer={{ pb: 16 }} isOpen={isOpen} onClose={onClose}>
      <Flex direction="column" justify="space-between" gap={3} mb={8}>
        <Flex align="center" gap={4}>
          <Heading size="md" color="brand.500" _dark={{ color: "brand.100" }}>
            {t(adjustmentType)}
          </Heading>
          <Spacer />
          <Tooltip label={t("status")}>
            <Badge variant="subtle">
              {data?.deletedAt ? t("inactive") : t(data?.status || "")}
            </Badge>
          </Tooltip>
          {data?.priceType && (
            <Tooltip label={t("price type")}>
              <Badge
                colorScheme={BADGE_TYPE_COLORS[data.priceType]}
                variant="subtle"
              >
                {getPriceTypeTranslation(data.priceType)}
              </Badge>
            </Tooltip>
          )}
        </Flex>
        <Heading size="sm" color="gray.600" _dark={{ color: "gray.300" }}>
          {data?.causer?.fullname ?? ""}
        </Heading>
      </Flex>
      <Flex direction={{ base: "column", md: "row" }} gap={3} mb={8}>
        <Flex align="center" gap={4} flex={1}>
          <Tooltip label={t("price")}>
            <Flex align="center">
              <Icon as={IoPricetagOutline} />
            </Flex>
          </Tooltip>
          <Text fontSize="sm" fontWeight="300">
            {data?.priceType === "dynamic_percentage"
              ? `${data?.price ?? 0}%`
              : formatCurrency(language, currency, data?.price ?? 0)}
          </Text>
        </Flex>
        <Flex align="center" gap={4} flex={1}>
          <Tooltip label={t("created at")}>
            <Flex align="center">
              <Icon as={MdOutlineDateRange} />
            </Flex>
          </Tooltip>
          <Text fontSize="sm" fontWeight="300">
            {toDayjs(data?.createdAt).format(DATETIME_FORMAT) || ""}
          </Text>
        </Flex>
      </Flex>
      <Flex direction={{ base: "column", md: "row" }} gap={2} mb={8}>
        <Flex align="center" gap={4} flex={1}>
          <Tooltip label={t("execution date")}>
            <Flex align="center">
              <Icon as={MdOutlineUpdate} />
            </Flex>
          </Tooltip>
          <Text fontSize="sm" fontWeight="300">
            {toDayjs(data?.executionDate).format(DATE_FORMAT) || ""}
          </Text>
        </Flex>
        <Flex align="center" gap={4} flex={1}>
          <Tooltip label={t("description")}>
            <Flex align="center">
              <Icon as={MdOutlineTextFields} />
            </Flex>
          </Tooltip>
          <Text fontSize="sm" fontWeight="300">
            {data?.description || "-"}
          </Text>
        </Flex>
      </Flex>
      <DataTable size="md" data={data?.rows || []} columns={columns} />
    </Modal>
  );
};

export default ViewModal;
