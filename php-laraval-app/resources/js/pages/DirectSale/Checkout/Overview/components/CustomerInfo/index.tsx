import {
  Box,
  Button,
  Card,
  CardBody,
  CardHeader,
  Flex,
  Heading,
  Icon,
  useDisclosure,
} from "@chakra-ui/react";
import { useEffect, useMemo, useState } from "react";
import { useTranslation } from "react-i18next";
import { AiOutlineSearch } from "react-icons/ai";

import Autocomplete from "@/components/Autocomplete";
import CustomerTypeSelectionModal from "@/components/CustomerTypeSelectionModal";

import { useGetCashierCustomers } from "@/services/customer";

import User from "@/types/user";

import { CustomerSale } from "../..";
import InfoRow from "./components/InfoRow";

interface CustomerInfoProps {
  setCustomerSale: (customer?: CustomerSale) => void;
  customerSale?: CustomerSale;
}

const CustomerInfo = ({ setCustomerSale, customerSale }: CustomerInfoProps) => {
  const { t } = useTranslation();

  const [customerQuery, setCustomerQuery] = useState("");
  const [user, setUser] = useState<User>();

  const { isOpen, onOpen, onClose } = useDisclosure();

  const { data: customers, isFetching: isFetchingCustomers } =
    useGetCashierCustomers({
      request: {
        orFilters: [
          {
            like: {
              fullname: customerQuery,
              cellphone: customerQuery.replace(/^(0|\+46)/, "46"),
              id: customerQuery || customerSale?.userId,
            },
          },
        ],
        size: 20,
        pagination: "cursor",
        show: "active",
        include: ["customers"],
        only: [
          "id",
          "fullname",
          "cellphone",
          "formattedCellphone",
          "type",
          "identityNumber",
          "customers.membershipType",
          "customers.type",
          "customers.id",
        ],
      },
      query: {
        enabled: customerQuery.length >= 2 || !!customerSale?.userId,
        staleTime: 5000,
      },
    });

  const customerOptions = useMemo(
    () =>
      customers?.map((customer) => {
        const formattedPhone = customer.cellphone
          ? customer.cellphone.replace(/^(46)/, "+46 (0)")
          : "";

        return {
          label: `${customer.id} | ${customer.fullname} | ${formattedPhone}`,
          value: customer.id,
        };
      }) ?? [],
    [customers],
  );

  const formattedQuery = useMemo(
    () => customerQuery.replace(/^(0|46|\+46)/, "+46 (0)"),
    [customerQuery],
  );

  const handleCustomerSelect = (value: string) => {
    const customer = customers?.find(
      (customer) => customer.id === Number(value),
    );

    setUser(customer);
  };

  const customer = useMemo(
    () => user?.customers?.find((customer) => customer.type === "primary"),
    [user],
  );

  useEffect(() => {
    if (user && customer) {
      setCustomerSale({ userId: user?.id, customerId: customer?.id });
      return;
    }

    setCustomerSale(customerSale);
  }, [user, customer]);

  useEffect(() => {
    if (customerOptions.length > 0 && customerSale?.userId) {
      setCustomerQuery(customerSale.userId.toString());
      handleCustomerSelect(customerSale.userId.toString());
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [customerOptions, customerSale?.userId]);

  return (
    <>
      <Card role="region" aria-label={t("customer info")}>
        <CardHeader>
          <Heading as="h2" size="sm">
            {t("customer info")}
          </Heading>
        </CardHeader>
        <CardBody fontSize="sm">
          <Flex direction="column" gap={4} justifyContent="space-between">
            <Autocomplete
              labelText={t("search")}
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
                        onOpen();
                      }}
                    >
                      {t("create new customer")}
                    </Button>
                  </Box>
                )
              }
              suffix={<Icon as={AiOutlineSearch} />}
              container={{
                display: "grid",
                gridTemplateColumns: "1fr 2fr",
                alignItems: "center",
                rowGap: 1,
                columnGap: 4,
              }}
              label={{
                m: 0,
              }}
              isRequired
              freeMode
            />

            <InfoRow label={t("id")} value={user?.id} />
            <InfoRow
              label={t("customer type")}
              value={t(customer?.membershipType ?? "")}
            />
            <InfoRow
              label={t("identity number")}
              value={user?.identityNumber}
            />
            <InfoRow label={t("name")} value={user?.fullname} />
            <InfoRow label={t("phone")} value={user?.formattedCellphone} />
          </Flex>
        </CardBody>
      </Card>
      <CustomerTypeSelectionModal
        isOpen={isOpen}
        onClose={onClose}
        data={{
          name: customerQuery,
          isNewPage: true,
        }}
      />
    </>
  );
};

export default CustomerInfo;
