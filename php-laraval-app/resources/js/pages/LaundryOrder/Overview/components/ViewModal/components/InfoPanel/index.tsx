import {
  Badge,
  Flex,
  Heading,
  Icon,
  Spacer,
  TabPanel,
  TabPanelProps,
  Text,
  Tooltip,
} from "@chakra-ui/react";
import { useTranslation } from "react-i18next";
import { AiOutlineShop } from "react-icons/ai";
import { LuMapPin, LuPhone, LuSettings } from "react-icons/lu";

import { LaundryOrder } from "@/types/laundryOrder";

import { toDayjs } from "@/utils/datetime";

const statusColors = {
  pending: "gray",
  in_progress_pickup: "yellow",
  picked_up: "purple",
  in_progress_store: "cyan",
  in_progress_laundry: "cyan",
  in_progress_delivery: "cyan",
  delivered: "teal",
  done: "blue",
  paid: "purple",
  closed: "green",
};

const typeColors: Record<string, string> = {
  company: "blue",
  private: "orange",
};

interface InfoPanelProps extends Omit<TabPanelProps, "laundryOrder"> {
  laundryOrder: LaundryOrder;
}

const InfoPanel = ({ laundryOrder, ...props }: InfoPanelProps) => {
  const { t } = useTranslation();

  const phone = laundryOrder.user?.formattedCellphone ?? "-";
  const customerAddress = laundryOrder.customer?.address?.fullAddress ?? "-";

  return (
    <TabPanel {...props}>
      <Flex direction="column" justify="space-between" gap={3} mb={8}>
        <Flex align="center" gap={4}>
          <Heading size="md" color="brand.500" _dark={{ color: "brand.100" }}>
            {laundryOrder.customer?.name ?? ""}
          </Heading>
          <Spacer />
          <Tooltip label={t("type")}>
            <Badge
              colorScheme={typeColors[laundryOrder.customerType]}
              variant="subtle"
            >
              {t(laundryOrder.customerType)}
            </Badge>
          </Tooltip>
          <Tooltip label={t("status")}>
            <Badge
              colorScheme={statusColors[laundryOrder.status]}
              variant="subtle"
            >
              {t(laundryOrder.status)}
            </Badge>
          </Tooltip>
        </Flex>
        <Heading
          size="sm"
          color="gray.600"
          fontWeight="medium"
          _dark={{ color: "gray.300" }}
        >
          {toDayjs(laundryOrder.createdAt ?? "").format("LLL")}
        </Heading>
      </Flex>
      <Flex
        direction={{ base: "column", md: "row" }}
        gap={{ base: 2, md: 4 }}
        mb={8}
      >
        <Flex direction="column" justify="space-between" flex={1} gap={3}>
          <Flex align="center" gap={4}>
            <Tooltip label={t("phone")}>
              <Flex align="center">
                <Icon as={LuPhone} />
              </Flex>
            </Tooltip>
            <Text fontSize="sm" fontWeight="300">
              {phone}
            </Text>
          </Flex>
          <Flex align="center" gap={4}>
            <Tooltip label={t("address")}>
              <Flex align="center">
                <Icon as={LuMapPin} />
              </Flex>
            </Tooltip>
            <Text fontSize="sm" fontWeight="300">
              {customerAddress}
            </Text>
          </Flex>
        </Flex>
        <Flex direction="column" justify="space-between" flex={1} gap={3}>
          <Flex align="center" gap={4}>
            <Tooltip label={t("store")}>
              <Flex align="center">
                <Icon as={AiOutlineShop} />
              </Flex>
            </Tooltip>
            <Text fontSize="sm" fontWeight="300">
              {laundryOrder.store?.address?.address ?? ""}
            </Text>
          </Flex>

          <Flex align="center" gap={4}>
            <Tooltip label={t("delivery preference")}>
              <Flex align="center">
                <Icon as={LuSettings} />
              </Flex>
            </Tooltip>
            <Text fontSize="sm" fontWeight="300">
              {laundryOrder.laundryPreference?.name ?? ""}
            </Text>
          </Flex>
        </Flex>
      </Flex>
    </TabPanel>
  );
};

export default InfoPanel;
