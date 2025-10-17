import { Flex, Text } from "@chakra-ui/react";
import { useMemo } from "react";
import { Trans, useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import DataTable from "@/components/DataTable";

import { Cart as CartType } from "@/hooks/useCart";

import useAuthStore from "@/stores/auth";

import User from "@/types/user";

import { formatCurrency } from "@/utils/currency";
import { round } from "@/utils/number";

import getColumns from "./column";

interface CartProps {
  user?: User;
  cart: CartType;
  errors?: Record<string, string>;
}

const Cart = ({ user, cart, errors }: CartProps) => {
  const { t } = useTranslation();
  const { currency, language } = useAuthStore();

  const columns = useMemo(
    () => getColumns({ language, currency, t, errors }),
    [language, currency, t, errors],
  );
  const {
    products,
    totalRut,
    hasRut,
    fixedPrice,
    hasFixedPrice,
    totalPrice,
    laundryPreferencePrice,
  } = cart;

  const totalToPay = totalPrice + laundryPreferencePrice;
  const roundedTotalToPay = Math.round(totalToPay);
  const roundAmount = roundedTotalToPay - totalToPay;

  return (
    <>
      <Flex direction="column" gap={4}>
        <Text fontSize="lg" fontWeight="bold">
          {t("customer name cart", { name: user?.fullname })}
        </Text>
        <DataTable
          title={t("customer cart")}
          data={products}
          columns={columns}
          searchable={false}
          filterable={false}
          paginatable={false}
          serverSide={false}
          footerTotal={[
            ...(hasFixedPrice
              ? [
                  {
                    title: t("fixed price"),
                    value: fixedPrice,
                    formatter: (value: number) =>
                      formatCurrency(language, currency, value, 2),
                  },
                ]
              : []),
            ...(round(roundAmount) !== 0
              ? [
                  {
                    title: t("rounding"),
                    value: roundAmount,
                    formatter: (value: number) =>
                      formatCurrency(language, currency, value, 2),
                  },
                ]
              : []),
            {
              title: t("total to pay"),
              value: roundedTotalToPay,
              formatter: (value: number) =>
                formatCurrency(language, currency, value, 2),
            },
          ]}
          size="xs"
          maxHeight="full"
        />

        {hasFixedPrice && (
          <Alert
            status="info"
            title={t("info")}
            richMessage={
              <Trans
                i18nKey="cart products containing fixed price"
                values={{
                  total: formatCurrency(language, currency, fixedPrice, 2),
                }}
              />
            }
            fontSize="small"
          />
        )}

        {hasRut && (
          <>
            <Alert
              status="info"
              title={t("info")}
              message={t("cart products containing rut")}
              fontSize="small"
            />
            <Alert
              status="info"
              title={t("info")}
              richMessage={
                <Trans
                  i18nKey="cart products total rut"
                  values={{
                    total: formatCurrency(language, currency, totalRut, 2),
                  }}
                />
              }
              fontSize="small"
            />
          </>
        )}

        {roundAmount !== 0 && (
          <>
            <Alert
              status="info"
              title={t("info")}
              richMessage={
                <Trans
                  i18nKey="cart products total to pay rounded"
                  values={{
                    roundAmount: formatCurrency(
                      language,
                      currency,
                      roundAmount,
                      2,
                    ),
                  }}
                />
              }
              fontSize="small"
            />
          </>
        )}
      </Flex>
    </>
  );
};

export default Cart;
