import { Flex, Text, useConst } from "@chakra-ui/react";
import { Trans, useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import DataTable from "@/components/DataTable";

import useAuthStore from "@/stores/auth";

import { CartProduct } from "@/types/cartProduct";

import { formatCurrency } from "@/utils/currency";
import { round } from "@/utils/number";

import getColumns from "./column";

interface CartProps {
  cartProducts: CartProduct[];
  totalPrice: number;
}

const Cart = ({ cartProducts, totalPrice }: CartProps) => {
  const { t } = useTranslation();
  const { currency, language } = useAuthStore();
  const columns = useConst(getColumns({ language, currency, t }));

  const roundedTotalToPay = Math.round(totalPrice);
  const roundAmount = roundedTotalToPay - totalPrice;

  return (
    <>
      <Flex direction="column" gap={4}>
        <Text fontSize="lg" fontWeight="bold">
          {t("shopping cart")}
        </Text>
        <DataTable
          title={t("shopping cart")}
          data={cartProducts}
          columns={columns}
          searchable={false}
          filterable={false}
          paginatable={false}
          serverSide={false}
          footerTotal={[
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
          useWindowScroll
        />

        {round(roundAmount) !== 0 && (
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
