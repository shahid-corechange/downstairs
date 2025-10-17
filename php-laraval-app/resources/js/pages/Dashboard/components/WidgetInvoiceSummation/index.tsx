import {
  Card,
  CardBody,
  CardHeader,
  Heading,
  Text,
  useDisclosure,
} from "@chakra-ui/react";
import { useMemo } from "react";
import { Trans, useTranslation } from "react-i18next";

import { useGetInvoices } from "@/services/invoice";

import useAuthStore from "@/stores/auth";

import { formatCurrency } from "@/utils/currency";
import { toDayjs } from "@/utils/datetime";

import DetailModal from "./components/DetailModal";

interface InvoiceSummation {
  totalInvoiced: number;
  totalRut: number;
  invoiceCount: number;
}

const WidgetInvoiceSummation = () => {
  const { t } = useTranslation();
  const { isOpen, onOpen, onClose } = useDisclosure();

  const language = useAuthStore((state) => state.language);
  const currency = useAuthStore((state) => state.currency);

  const currentMonthLastDay = toDayjs()
    .endOf("month")
    .tz("UTC", true)
    .toISOString();
  const previousMonthFirstDay = toDayjs()
    .subtract(1, "month")
    .startOf("month")
    .tz("UTC", true)
    .toISOString();

  const invoices = useGetInvoices({
    request: {
      filter: {
        between: {
          sentAt: [previousMonthFirstDay, currentMonthLastDay],
        },
        in: {
          status: ["paid", "sent"],
        },
      },
      only: ["sentAt", "totalRut", "totalInvoiced"],
      sort: { sentAt: "desc" },
      size: -1,
    },
  });

  const summation = useMemo(() => {
    if (!invoices.data) {
      return undefined;
    }

    const groupedSummation = invoices.data.reduce<
      Record<string, InvoiceSummation>
    >((acc, item) => {
      const period = toDayjs(item.sentAt).format("YYYY-MM");

      if (!acc[period]) {
        acc[period] = {
          totalInvoiced: 0,
          totalRut: 0,
          invoiceCount: 0,
        };
      }

      acc[period].totalInvoiced += item.totalInvoiced;
      acc[period].totalRut += item.totalRut;
      acc[period].invoiceCount += 1;

      return acc;
    }, {});

    const result = Object.values(groupedSummation)[0];

    if (!result) {
      return {
        totalInvoiced: 0,
        totalRut: 0,
        invoiceCount: 0,
      };
    }

    return result;
  }, [invoices.data]);

  return (
    <>
      <Card minH={143} textAlign="left" cursor="pointer" onClick={onOpen}>
        <CardHeader>
          <Heading size="sm">{t("invoice summation")}</Heading>
        </CardHeader>
        <CardBody fontSize="sm" paddingTop={0} alignContent="center">
          {!summation ? (
            t("loading") + "..."
          ) : (
            <>
              <Text>
                <Trans
                  i18nKey="invoice summation of invoiced widget text"
                  values={{
                    total: formatCurrency(
                      language,
                      currency,
                      summation.totalInvoiced,
                    ),
                  }}
                />
              </Text>
              <Text>
                <Trans
                  i18nKey="invoice summation of RUT widget text"
                  values={{
                    total: formatCurrency(
                      language,
                      currency,
                      summation.totalRut,
                    ),
                  }}
                />
              </Text>
              <Text>
                <Trans
                  i18nKey="invoice summation of total invoices widget text"
                  values={{
                    total: summation.invoiceCount,
                  }}
                />
              </Text>
            </>
          )}
        </CardBody>
      </Card>

      <DetailModal isOpen={isOpen} onClose={onClose} />
    </>
  );
};

export default WidgetInvoiceSummation;
