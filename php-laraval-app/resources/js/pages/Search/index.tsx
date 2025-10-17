import {
  Box,
  Breadcrumb,
  Button,
  Flex,
  Heading,
  Icon,
  useDisclosure,
} from "@chakra-ui/react";
import { Head, router, usePage } from "@inertiajs/react";
import dayjs from "dayjs";
import { useMemo, useState } from "react";
import { useTranslation } from "react-i18next";
import { AiOutlineSearch } from "react-icons/ai";

import Autocomplete from "@/components/Autocomplete";
import CustomerTypeSelectionModal from "@/components/CustomerTypeSelectionModal";

import { DATE_FORMAT } from "@/constants/datetime";

import CashierLayout from "@/layouts/Cashier";

import { useGetCashierOrders } from "@/services/cashierOrder";
import { useGetCashierCustomers } from "@/services/customer";

import { PageProps } from "@/types";

const SearchOverviewPage = () => {
  const { t } = useTranslation();
  const { storeId } = usePage<PageProps>().props;

  const [customerQuery, setCustomerQuery] = useState("");
  const [laundryOrderQuery, setLaundryOrderQuery] = useState("");

  const { isOpen, onOpen, onClose } = useDisclosure();

  const { data: customers, isFetching: isFetchingCustomers } =
    useGetCashierCustomers({
      request: {
        orFilters: [
          {
            like: {
              fullname: customerQuery,
              cellphone: customerQuery.replace(/^(0|\+46)/, "46"),
              id: customerQuery,
            },
          },
        ],
        size: 20,
        pagination: "cursor",
        show: "active",
        only: ["id", "fullname", "cellphone"],
      },
      query: {
        enabled: customerQuery.length >= 2,
        staleTime: 5000,
      },
    });

  const { data: laundryOrders, isFetching: isFetchingLaundryOrders } =
    useGetCashierOrders({
      request: {
        filter: {
          like: {
            id: laundryOrderQuery,
          },
          eq: {
            storeId: storeId ?? undefined,
          },
        },
        size: 20,
        pagination: "cursor",
        show: "active",
        include: ["user"],
        only: ["id", "userId", "user.fullname", "dueAt"],
      },
      query: {
        enabled: !!laundryOrderQuery && !!storeId,
        staleTime: 5000,
      },
    });

  const customerOptions = useMemo(() => {
    if (customerQuery.length < 2) {
      return [];
    }

    return (
      customers?.map((customer) => {
        const formattedPhone = customer.cellphone
          ? customer.cellphone.replace(/^(46)/, "+46 (0)")
          : "";

        return {
          label: `${customer.id} | ${customer.fullname} | ${formattedPhone}`,
          value: customer.id,
        };
      }) ?? []
    );
  }, [customers, customerQuery]);

  const laundryOrderOptions = useMemo(() => {
    if (!laundryOrderQuery) {
      return [];
    }

    return (
      laundryOrders?.data?.map((laundryOrder) => {
        const dueAt = laundryOrder.dueAt
          ? dayjs(laundryOrder.dueAt).format(DATE_FORMAT)
          : "-";

        return {
          label: `${laundryOrder.id} | ${laundryOrder.user?.fullname} | ${dueAt}`,
          value: `${laundryOrder.id}-${laundryOrder.userId}`,
        };
      }) ?? []
    );
  }, [laundryOrders, laundryOrderQuery]);

  const formattedQuery = useMemo(
    () => customerQuery.replace(/^(0|46|\+46)/, "+46 (0)"),
    [customerQuery],
  );

  const handleCustomerSelect = (value?: string) => {
    if (value) {
      router.get(`/cashier/customers/${value}/cart`);
    } else {
      onOpen();
    }
  };

  const handleLaundryOrderSelect = (value?: string, customerId?: string) => {
    if (value && customerId) {
      router.get(`/cashier/customers/${customerId}/orders/${value}`);
    }
  };

  return (
    <>
      <Head>
        <title>{t("search customer")}</title>
      </Head>
      <CashierLayout content={{ p: 6 }}>
        <Flex direction="column" mb={4}>
          <Breadcrumb />
        </Flex>

        <Flex
          justifyContent="space-around"
          alignItems="center"
          minH="40vh"
          gap={8}
        >
          <Flex direction="column" w="xl" gap={8} alignItems="center">
            <Heading size="lg">{t("search customer")}</Heading>
            <Autocomplete
              placeholder={t("search customer placeholder")}
              options={customerOptions}
              isLoading={isFetchingCustomers}
              value={customerQuery}
              customFilter={formattedQuery}
              onChangeDebounce={(value) => setCustomerQuery(String(value))}
              onChange={(e) => handleCustomerSelect(e.target.value)}
              stickyFooterOption={(onCloseOptions) =>
                customerQuery &&
                !isFetchingCustomers && (
                  <Box
                    borderTop="1px"
                    borderColor="gray.100"
                    _dark={{ borderColor: "gray.600" }}
                  >
                    <Button
                      variant="ghost"
                      size="md"
                      w="full"
                      display="inline-block"
                      fontSize="small"
                      fontWeight="normal"
                      textAlign="left"
                      rounded="none"
                      whiteSpace="pre-wrap"
                      onClick={() => {
                        onCloseOptions();
                        handleCustomerSelect();
                      }}
                    >
                      {t("create new customer")}
                    </Button>
                  </Box>
                )
              }
              suffix={<Icon as={AiOutlineSearch} />}
              clearOnSelect
              freeMode
            />
          </Flex>

          <Flex direction="column" w="xl" gap={8} alignItems="center">
            <Heading size="lg">{t("search order")}</Heading>
            <Autocomplete
              placeholder={t("search order placeholder")}
              options={laundryOrderOptions}
              isLoading={isFetchingLaundryOrders}
              value={laundryOrderQuery}
              onChangeDebounce={(value) => setLaundryOrderQuery(String(value))}
              onChange={(e) => {
                const value = e.target.value;
                const [laundryOrderId, customerId] = value.split("-");
                handleLaundryOrderSelect(laundryOrderId, customerId);
              }}
              suffix={<Icon as={AiOutlineSearch} />}
              clearOnSelect
              freeMode
            />
          </Flex>
        </Flex>
      </CashierLayout>
      <CustomerTypeSelectionModal
        isOpen={isOpen}
        onClose={onClose}
        data={{
          name: customerQuery,
          isNewPage: false,
        }}
      />
    </>
  );
};

export default SearchOverviewPage;
