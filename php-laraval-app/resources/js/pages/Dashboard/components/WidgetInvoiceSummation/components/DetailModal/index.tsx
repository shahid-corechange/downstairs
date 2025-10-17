import { Box, ListItem, UnorderedList, useConst } from "@chakra-ui/react";
import { useEffect, useMemo, useState } from "react";
import { Trans, useTranslation } from "react-i18next";
import { RiExternalLinkLine } from "react-icons/ri";

import Alert from "@/components/Alert";
import DataTable from "@/components/DataTable";
import Modal from "@/components/Modal";
import YearPicker from "@/components/YearPicker";

import { CURRENT_YEAR, DATETIME_FORMAT } from "@/constants/datetime";

import { useGetInvoices } from "@/services/invoice";

import InvoiceSummation from "@/types/invoiceSummation";

import { hasPermission } from "@/utils/authorization";
import { toDayjs } from "@/utils/datetime";

import getColumns from "./column";

interface DetailModalProps {
  isOpen: boolean;
  onClose: () => void;
}

const DetailModal = ({ isOpen, onClose }: DetailModalProps) => {
  const { t } = useTranslation();
  const columns = useConst(getColumns(t));

  const [selectedYear, setSelectedYear] = useState(CURRENT_YEAR);

  const invoices = useGetInvoices({
    request: {
      filter: {
        eq: {
          year: selectedYear,
        },
        in: {
          status: ["paid", "sent"],
        },
      },
      only: [
        "month",
        "year",
        "sentAt",
        "totalGross",
        "totalNet",
        "totalVat",
        "totalIncludeVat",
        "totalRut",
        "totalInvoiced",
      ],
      sort: { sentAt: "desc" },
      size: -1,
    },
    query: {
      enabled: isOpen,
    },
  });

  const invoiceSummations = useMemo(() => {
    if (!invoices.data) {
      return undefined;
    }

    const groupedSummation = invoices.data.reduce<
      Record<string, InvoiceSummation>
    >((acc, item) => {
      const paddedMonth = `${item.month}`.padStart(2, "0");
      const invoicePeriod = `${item.year}-${paddedMonth}`;
      const sentPeriod = toDayjs(item.sentAt).format("YYYY-MM");

      if (!acc[`${invoicePeriod}-${sentPeriod}`]) {
        acc[`${invoicePeriod}-${sentPeriod}`] = {
          invoicePeriod,
          sentPeriod,
          totalGross: 0,
          totalNet: 0,
          totalVat: 0,
          totalRut: 0,
          totalIncludeVat: 0,
          totalInvoiced: 0,
          invoiceCount: 0,
        };
      }

      acc[`${invoicePeriod}-${sentPeriod}`].totalGross += item.totalGross;
      acc[`${invoicePeriod}-${sentPeriod}`].totalNet += item.totalNet;
      acc[`${invoicePeriod}-${sentPeriod}`].totalVat += item.totalVat;
      acc[`${invoicePeriod}-${sentPeriod}`].totalRut += item.totalRut;
      acc[`${invoicePeriod}-${sentPeriod}`].totalIncludeVat +=
        item.totalIncludeVat;
      acc[`${invoicePeriod}-${sentPeriod}`].totalInvoiced += item.totalInvoiced;
      acc[`${invoicePeriod}-${sentPeriod}`].invoiceCount += 1;

      return acc;
    }, {});

    return Object.values(groupedSummation);
  }, [invoices.data]);

  useEffect(() => {
    if (isOpen) {
      setSelectedYear(CURRENT_YEAR);
    }
  }, [isOpen]);

  return (
    <Modal
      title={t("invoice summation")}
      contentContainer={{ maxW: "100rem" }}
      isOpen={isOpen}
      onClose={onClose}
    >
      <Alert
        status="info"
        title={t("info")}
        richMessage={
          <UnorderedList>
            <ListItem>
              <Trans i18nKey="total gross explanation" />
            </ListItem>
            <ListItem>
              <Trans i18nKey="total net explanation" />
            </ListItem>
            <ListItem>
              <Trans i18nKey="total including vat explanation" />
            </ListItem>
            <ListItem>
              <Trans i18nKey="total invoiced explanation" />
            </ListItem>
          </UnorderedList>
        }
        fontSize="small"
        mb={6}
      />
      <Box mb={4} maxW="200px">
        <YearPicker
          value={selectedYear.toString()}
          onChange={(e) => {
            setSelectedYear(Number(e.target.value));
          }}
        />
      </Box>
      <DataTable
        data={invoiceSummations || []}
        size="md"
        columns={columns}
        searchable={false}
        isLoading={!invoiceSummations}
        filterable={false}
        actions={[
          {
            label: t("invoices"),
            icon: RiExternalLinkLine,
            isHidden: !hasPermission("invoices index"),
            onClick: (row) => {
              const [year, month] = row.original.invoicePeriod.split("-");
              const startSentPeriod = toDayjs(row.original.sentPeriod, false)
                .startOf("month")
                .format(DATETIME_FORMAT);
              const endSentPeriod = toDayjs(row.original.sentPeriod, false)
                .endOf("month")
                .format(DATETIME_FORMAT);
              window.open(
                `/invoices?year.eq=${year}&month.eq=${Number(
                  month,
                )}&status.in=sent,paid&sentAt.between=${startSentPeriod},${endSentPeriod}`,
                "_blank",
              );
            },
          },
        ]}
      />
    </Modal>
  );
};

export default DetailModal;
