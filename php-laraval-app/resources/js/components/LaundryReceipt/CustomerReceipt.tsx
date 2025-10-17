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
import { forwardRef, useMemo } from "react";
import { Trans, useTranslation } from "react-i18next";
import { FaEnvelope, FaPhoneAlt } from "react-icons/fa";

import {
  DATETIME_FORMAT,
  DATE_FORMAT,
  TIME_12_HOUR_FORMAT,
} from "@/constants/datetime";
import { PaymentMethod } from "@/constants/paymentMethod";
import { COMPANY_INFO } from "@/constants/store";

import useAuthStore from "@/stores/auth";

import { LaundryOrder } from "@/types/laundryOrder";

import { formatCurrency } from "@/utils/currency";

import PrintText from "./components/PrintText";

type CustomerReceiptProps = {
  laundryOrder: LaundryOrder;
};

const CustomerReceipt = forwardRef<HTMLDivElement, CustomerReceiptProps>(
  (props, ref) => {
    const { t } = useTranslation();
    const { laundryOrder } = props;
    const { currency, language } = useAuthStore.getState();

    const hasRut = useMemo(
      () => laundryOrder.products?.some((product) => product.hasRut),
      [laundryOrder.products],
    );

    const totalRut = hasRut ? laundryOrder.totalRut : 0;

    const completionDate = laundryOrder.dueAt;
    const laundryPreference = laundryOrder.laundryPreference;
    const preferenceAmount = laundryOrder.preferenceAmount;

    const nett = laundryOrder.totalPriceWithVat + preferenceAmount;
    const gross =
      laundryOrder.totalToPay + preferenceAmount + laundryOrder.roundAmount;

    const totalAmount = useMemo(
      () =>
        laundryOrder.products?.reduce(
          (acc, product) => acc + product.quantity,
          laundryPreference ? 1 : 0,
        ),
      [laundryOrder.products, laundryPreference],
    );

    const paymentMethod =
      laundryOrder.paymentMethod === PaymentMethod.CREDIT_CARD
        ? t("cash invoice")
        : t("invoice");

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
            Downstairs {laundryOrder.store?.name}
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
            <ListItem>{COMPANY_INFO.postalCode}</ListItem>
            <ListItem>
              <Icon as={FaPhoneAlt} /> {COMPANY_INFO.phone}
            </ListItem>
            <ListItem>
              <Icon as={FaEnvelope} /> {COMPANY_INFO.email}
            </ListItem>
            <ListItem>Org. nr {COMPANY_INFO.orgNumber}</ListItem>
            <ListItem fontSize="2xs">Godkänd för F-skatt</ListItem>
            <ListItem fontWeight="extrabold" mt={2}>
              {t("store")}: {laundryOrder.store?.name},{" "}
              {laundryOrder.causer?.fullname}
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
        {/* {laundryOrder.status !== "paid" && (
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
                # {laundryOrder.id}
              </Text>
            </ListItem>
            <ListItem
              display="flex"
              flexDirection="row"
              alignItems="end"
              gap={2}
            >
              <Text fontSize="xs">{t("submission date")}:</Text>
              <Text fontSize="xs">
                {dayjs(laundryOrder.createdAt).format(DATETIME_FORMAT)}
              </Text>
            </ListItem>
            <ListItem
              display="flex"
              flexDirection="row"
              alignItems="end"
              gap={2}
            >
              <Text fontSize="xs">{t("completion date")}:</Text>
              {completionDate && (
                <Text fontSize="2xl" fontWeight="normal" lineHeight={1}>
                  {dayjs(completionDate).format(DATE_FORMAT)}
                </Text>
              )}
            </ListItem>
            <ListItem>
              <Text fontSize="2xs">
                <Trans
                  i18nKey="order completed at"
                  values={{
                    time: completionDate
                      ? dayjs(completionDate).format(TIME_12_HOUR_FORMAT)
                      : " - ",
                  }}
                />
              </Text>
            </ListItem>
            <ListItem
              display="flex"
              flexDirection="row"
              alignItems="end"
              gap={2}
            >
              <Text fontSize="xs">{t("payment method")}:</Text>
              <Text fontSize="xs">{paymentMethod}</Text>
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
            {laundryOrder.products?.map((product, index) => (
              <UnorderedList key={index} styleType="none" spacing={0} mx={0}>
                <ListItem display="flex" gap={1}>
                  <Text minW="8px">{product.quantity}</Text>
                  <Text>x</Text>
                  <Text flex={1}>{product.name}</Text>
                  <Text>
                    {formatCurrency(
                      language,
                      currency,
                      product.totalPriceWithVat,
                    )}
                  </Text>
                </ListItem>
                {product.totalDiscountAmount > 0 && (
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
                        product.totalDiscountAmount,
                      )}`}
                    </PrintText>
                  </ListItem>
                )}
                {product.hasRut && (
                  <ListItem
                    display="flex"
                    justifyContent="space-between"
                    color="red.500"
                    gap={2}
                    pl={4}
                  >
                    <PrintText>{t("rut")}</PrintText>
                    <PrintText>
                      {formatCurrency(language, currency, -product.totalRut)}
                    </PrintText>
                  </ListItem>
                )}
              </UnorderedList>
            ))}

            {laundryPreference && (
              <UnorderedList styleType="none" spacing={0} mx={0}>
                <ListItem display="flex" gap={1}>
                  <Text minW="8px">1</Text>
                  <Text>x</Text>
                  <Text flex={1}>{laundryPreference.name}</Text>
                  <Text>
                    {formatCurrency(language, currency, preferenceAmount)}
                  </Text>
                </ListItem>
              </UnorderedList>
            )}

            <ListItem display="flex" justifyContent="space-between" my={2}>
              <Text>{t("nett")}</Text>
              <Text>{formatCurrency(language, currency, nett)}</Text>
            </ListItem>

            {Object.keys(laundryOrder.totalVat).length > 0 && (
              <>
                <ListItem>
                  <Text fontStyle="italic">{t("of which vat")}</Text>
                </ListItem>
                {Object.entries(laundryOrder.totalVat).map(([vat, amount]) => (
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
            {laundryOrder.totalDiscount > 0 && (
              <ListItem
                display="flex"
                justifyContent="space-between"
                color="red.500"
              >
                <PrintText>{t("discount")}</PrintText>
                <PrintText>
                  -
                  {formatCurrency(
                    language,
                    currency,
                    laundryOrder.totalDiscount,
                  )}
                </PrintText>
              </ListItem>
            )}
            {hasRut && (
              <ListItem
                display="flex"
                justifyContent="space-between"
                color="red.500"
              >
                <PrintText>{t("rut")}</PrintText>
                <PrintText>
                  {formatCurrency(language, currency, -totalRut)}
                </PrintText>
              </ListItem>
            )}
            {laundryOrder.roundAmount && laundryOrder.roundAmount !== 0 && (
              <ListItem display="flex" justifyContent="space-between">
                <Text>{t("rounding")}</Text>
                <Text>
                  {formatCurrency(language, currency, laundryOrder.roundAmount)}
                </Text>
              </ListItem>
            )}
            <ListItem display="flex" justifyContent="space-between" mt={2}>
              <Text>{t("gross")}</Text>
              <Text fontWeight="extrabold">
                {formatCurrency(language, currency, gross)}
              </Text>
            </ListItem>
          </UnorderedList>
        </Flex>

        <Divider sx={{ "@media print": { borderColor: "#eee" } }} />

        <Flex direction="column" gap={1}>
          <Text fontSize="xs" fontWeight="extrabold" textDecoration="underline">
            {t("receipt customer information")}
          </Text>

          <UnorderedList styleType="none" spacing={0} fontSize="xs" mx={0}>
            <ListItem>{laundryOrder.user?.fullname}</ListItem>
            {laundryOrder.customer?.address?.fullAddress && (
              <ListItem>{laundryOrder.customer.address.fullAddress}</ListItem>
            )}
            {laundryOrder.customer?.address?.postalCode && (
              <ListItem>{laundryOrder.customer.address.postalCode}</ListItem>
            )}
            <ListItem>
              <Icon as={FaPhoneAlt} /> {laundryOrder.user?.formattedCellphone}
            </ListItem>
          </UnorderedList>
        </Flex>

        <Divider sx={{ "@media print": { borderColor: "#eee" } }} />

        {hasRut && (
          <>
            <Flex direction="column" gap={0}>
              <PrintText fontSize="xs" fontWeight="extrabold" color="red.500">
                {t("receipt note contains RUT amount")}
              </PrintText>
              <PrintText fontSize="md" color="red.500">
                {`${t("amount")}: ${formatCurrency(
                  language,
                  currency,
                  totalRut,
                )}`}
              </PrintText>
            </Flex>

            <Divider sx={{ "@media print": { borderColor: "#eee" } }} />
          </>
        )}

        <Text fontSize="xs" fontStyle="italic">
          <Trans
            i18nKey="receipt term"
            values={{
              name: laundryOrder.store?.name,
            }}
          />
        </Text>

        <Divider sx={{ "@media print": { borderColor: "#eee" } }} />

        <Text fontSize="md" fontWeight="thin">
          {t("thanks for visit")}
        </Text>
      </Flex>
    );
  },
);

export default CustomerReceipt;
