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
import { useMemo } from "react";
import { useTranslation } from "react-i18next";
import { GoNote } from "react-icons/go";
import {
  HiOutlineClock,
  HiOutlineCurrencyDollar,
  HiOutlineHome,
} from "react-icons/hi2";
import { LuUser2 } from "react-icons/lu";

import useAuthStore from "@/stores/auth";

import Order from "@/types/order";

import { formatCurrency } from "@/utils/currency";
import { toDayjs } from "@/utils/datetime";

const statusColors = {
  done: "green",
  cancel: "red",
  draft: "gray",
  progress: "orange",
};

const typeColors: Record<string, string> = {
  company: "blue",
  private: "orange",
};

interface InfoPanelProps extends Omit<TabPanelProps, "order"> {
  order: Order;
}

const InfoPanel = ({ order, ...props }: InfoPanelProps) => {
  const { t } = useTranslation();
  const { currency, language } = useAuthStore.getState();

  const totalPayment = useMemo(
    () =>
      order?.rows?.reduce((acc, value) => {
        const price = value.priceWithVat * value.quantity;
        const discount = price * ((value.discountPercentage ?? 0) / 100);
        return acc + price - discount;
      }, 0) ?? 0,
    [order],
  );

  const getOrderName = () => {
    if (order?.service?.name) {
      return order.service.name;
    }

    switch (order?.orderableType) {
      case "App\\Models\\LaundryOrder":
        return t("laundry");
      case "App\\Models\\StoreSale":
        return t("store");
      default:
        return "";
    }
  };

  const orderName = getOrderName();

  return (
    <TabPanel {...props}>
      <Flex direction="column" justify="space-between" gap={3} mb={8}>
        <Flex align="center" gap={4}>
          <Heading size="md" color="brand.500" _dark={{ color: "brand.100" }}>
            {orderName}
          </Heading>
          <Spacer />
          <Tooltip label={t("type")}>
            <Badge
              colorScheme={typeColors[order?.customer?.membershipType ?? ""]}
              variant="subtle"
            >
              {t(order?.customer?.membershipType ?? "")}
            </Badge>
          </Tooltip>
          <Tooltip label={t("status")}>
            <Badge colorScheme={statusColors[order.status]} variant="subtle">
              {t(`order status ${order.status}`)}
            </Badge>
          </Tooltip>
        </Flex>
        <Heading
          size="sm"
          color="gray.600"
          fontWeight="medium"
          _dark={{ color: "gray.300" }}
        >
          {toDayjs(order.orderedAt).format("LLL")}
        </Heading>
      </Flex>
      <Flex
        direction={{ base: "column", md: "row" }}
        gap={{ base: 3, md: 6 }}
        mb={8}
      >
        <Flex direction="column" justify="space-between" flex={1} gap={3}>
          <Flex align="center" gap={4}>
            <Tooltip label={t("buyer")}>
              <Flex align="center">
                <Icon as={LuUser2} />
              </Flex>
            </Tooltip>
            <Text fontSize="sm" fontWeight="300">
              {order?.user?.fullname ?? ""}
            </Text>
          </Flex>
          <Flex align="center" gap={4}>
            <Tooltip label={t("customer")}>
              <Flex align="center">
                <Icon as={LuUser2} />
              </Flex>
            </Tooltip>
            <Text fontSize="sm" fontWeight="300">
              {order?.customer?.name ?? ""}
            </Text>
          </Flex>
          <Flex align="center" gap={4}>
            <Tooltip label={t("customer address")}>
              <Flex align="center">
                <Icon as={HiOutlineHome} />
              </Flex>
            </Tooltip>
            <Text fontSize="sm" fontWeight="300">
              {order?.customer?.address?.fullAddress ?? ""}
            </Text>
          </Flex>
        </Flex>
        <Flex direction="column" justify="space-between" flex={1} gap={3}>
          <Flex align="center" gap={4}>
            <Tooltip label={t("total payment")}>
              <Flex align="center">
                <Icon as={HiOutlineCurrencyDollar} />
              </Flex>
            </Tooltip>
            <Text fontSize="sm" fontWeight="300">
              {formatCurrency(language, currency, totalPayment)}
            </Text>
          </Flex>
          <Flex align="center" gap={4}>
            <Tooltip label={t("paid by")}>
              <Flex align="center">
                <Icon as={GoNote} />
              </Flex>
            </Tooltip>
            <Text fontSize="sm" fontWeight="300" textTransform="capitalize">
              {order.paidBy}
            </Text>
          </Flex>
          <Flex align="center" gap={4}>
            <Tooltip label={t("paid at")}>
              <Flex align="center">
                <Icon as={HiOutlineClock} />
              </Flex>
            </Tooltip>
            <Text fontSize="sm" fontWeight="300">
              {order.paidAt ? toDayjs(order.paidAt).format("LLL") : "-"}
            </Text>
          </Flex>
        </Flex>
      </Flex>
    </TabPanel>
  );
};

export default InfoPanel;
