import {
  Center,
  Divider,
  Flex,
  Icon,
  Image,
  ListItem,
  Text,
  UnorderedList,
} from "@chakra-ui/react";
import dayjs from "dayjs";
import { forwardRef } from "react";
import { Trans, useTranslation } from "react-i18next";
import { FaEnvelope, FaPhoneAlt } from "react-icons/fa";

import { DATETIME_FORMAT } from "@/constants/datetime";
import { COMPANY_INFO } from "@/constants/store";

import useAuthStore from "@/stores/auth";

import StoreSale from "@/types/storeSale";

import { formatCurrency } from "@/utils/currency";

import PrintText from "./components/PrintText";

type CustomerReceiptProps = {
  storeSale: StoreSale;
};

const DirectSaleReceipt = forwardRef<HTMLDivElement, CustomerReceiptProps>(
  (props, ref) => {
    const { t } = useTranslation();
    const { storeSale } = props;
    const { currency, language } = useAuthStore.getState();

    const totalPrice = storeSale.totalPriceWithVat;
    const roundedTotalToPay = storeSale.roundedTotalToPay;

    const totalAmount =
      storeSale.products?.reduce(
        (acc, product) => acc + (product.quantity || 0),
        0,
      ) ?? 0;

    return (
      <Flex
        ref={ref}
        direction="column"
        gap={5}
        sx={{
          "@media print": {
            margin: "8px",
          },
        }}
      >
        <Flex direction="column" gap={2}>
          <Center>
            <Image
              src="/images/small-logo-b.png"
              alt="Logo"
              width={200}
              height={57}
              mx="auto"
            />
          </Center>

          <Text fontSize="md" textAlign="center">
            Downstairs {storeSale.store?.name}
          </Text>

          <UnorderedList
            styleType="none"
            textAlign="center"
            fontSize="xs"
            lineHeight={1.6}
            spacing={0}
            mx={0}
          >
            <ListItem>{COMPANY_INFO.address}</ListItem>
            <ListItem>
              {COMPANY_INFO.postalCode} {COMPANY_INFO.city}
            </ListItem>
            <ListItem>
              <Icon as={FaPhoneAlt} /> {COMPANY_INFO.phone}
            </ListItem>
            <ListItem>
              <Icon as={FaEnvelope} /> {COMPANY_INFO.email}
            </ListItem>
            <ListItem>Org. nr {COMPANY_INFO.orgNumber}</ListItem>
            <ListItem fontSize="2xs">Godkänd för F-skatt</ListItem>
            <ListItem fontWeight="extrabold" mt={2}>
              {t("store")}: {storeSale.store?.name},{" "}
              {storeSale.causer?.fullname}
            </ListItem>
          </UnorderedList>

          <Text
            fontSize="xl"
            textAlign="center"
            textTransform="uppercase"
            fontWeight="normal"
            mt={3}
          >
            ** {t("payment specification")} **
          </Text>
        </Flex>

        <Divider sx={{ "@media print": { borderColor: "#eee" } }} />

        {/* Disable for now */}
        {/* {storeSale.status !== "paid" && (
          <>
            <PrintText
              fontSize="xl"
              textAlign="center"
              textTransform="uppercase"
              fontWeight="normal"
              color="red.500"
            >
              ** {t("unpaid")} **
            </PrintText>

            <Divider sx={{ "@media print": { borderColor: "#eee" } }} />
          </>
        )} */}

        <Flex direction="column" gap={3}>
          <UnorderedList
            styleType="none"
            spacing={1}
            fontWeight="medium"
            mx={0}
          >
            <ListItem
              display="flex"
              flexDirection="row"
              alignItems="end"
              gap={2}
            >
              <Text fontSize="xs">{t("order number")}:</Text>
              <Text fontSize="2xl" fontWeight="medium" lineHeight={1}>
                # {storeSale.id}
              </Text>
            </ListItem>
            <ListItem
              display="flex"
              flexDirection="row"
              alignItems="end"
              gap={2}
            >
              <Text fontSize="xs">{t("order at")}:</Text>
              <Text fontSize="xs">
                {dayjs(storeSale.createdAt).format(DATETIME_FORMAT)}
              </Text>
            </ListItem>
          </UnorderedList>

          <Text size="sm">
            <Trans
              i18nKey="submitted articles with amount"
              values={{ amount: totalAmount }}
              components={{ u: <u /> }}
            />
          </Text>

          <UnorderedList styleType="none" spacing={0} fontSize="xs" mx={0}>
            {storeSale.products?.map((product, index) => (
              <UnorderedList key={index} styleType="none" spacing={0} mx={0}>
                <ListItem display="flex" gap={1}>
                  <Text minW="8px">{product.quantity}</Text>
                  <Text>x</Text>
                  <Text flex={1}>{product.name}</Text>
                  <Text>
                    {formatCurrency(language, currency, product.priceWithVat)}
                  </Text>
                </ListItem>
                {product.discountAmount > 0 && (
                  <ListItem
                    display="flex"
                    justifyContent="space-between"
                    color="red.500"
                    gap={2}
                    pl={4}
                  >
                    <PrintText>
                      {t("discount")} {Number(product.discount).toFixed(0)}%
                    </PrintText>
                    <PrintText>
                      {`-${formatCurrency(
                        language,
                        currency,
                        product.discountAmount,
                      )}`}
                    </PrintText>
                  </ListItem>
                )}
              </UnorderedList>
            ))}

            <ListItem display="flex" justifyContent="space-between" my={2}>
              <Text>{t("nett")}</Text>
              <Text>{formatCurrency(language, currency, totalPrice)}</Text>
            </ListItem>

            {Object.keys(storeSale.totalVat).length > 0 && (
              <>
                <ListItem>
                  <Text fontStyle="italic">{t("of which vat")}</Text>
                </ListItem>
                {Object.entries(storeSale.totalVat).map(([vat, amount]) => (
                  <ListItem
                    key={vat}
                    display="flex"
                    justifyContent="space-between"
                  >
                    <Text>
                      {t("vat")} {vat}%
                    </Text>
                    <Text>{formatCurrency(language, currency, amount)}</Text>
                  </ListItem>
                ))}
              </>
            )}
            {storeSale.totalDiscount > 0 && (
              <ListItem
                display="flex"
                justifyContent="space-between"
                color="red.500"
              >
                <PrintText>{t("discount")}</PrintText>
                <PrintText>
                  -{formatCurrency(language, currency, storeSale.totalDiscount)}
                </PrintText>
              </ListItem>
            )}
            {storeSale.roundAmount && storeSale.roundAmount !== 0 && (
              <ListItem display="flex" justifyContent="space-between" mt={2}>
                <Text>{t("rounding")}</Text>
                <Text>
                  {formatCurrency(language, currency, storeSale.roundAmount)}
                </Text>
              </ListItem>
            )}
            <ListItem display="flex" justifyContent="space-between" mt={2}>
              <Text>{t("gross")}</Text>
              <Text fontWeight="extrabold">
                {formatCurrency(language, currency, roundedTotalToPay)}
              </Text>
            </ListItem>
          </UnorderedList>
        </Flex>

        <Divider sx={{ "@media print": { borderColor: "#eee" } }} />

        <Text fontSize="md" fontWeight="thin">
          {t("thanks for visit")}
        </Text>
      </Flex>
    );
  },
);

export default DirectSaleReceipt;
