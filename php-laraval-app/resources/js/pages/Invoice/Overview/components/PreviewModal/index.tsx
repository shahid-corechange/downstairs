import {
  Button,
  Checkbox,
  Flex,
  IconButton,
  Spacer,
  Spinner,
  Table,
  Tbody,
  Td,
  Text,
  Textarea,
  Th,
  Thead,
  Tooltip,
  Tr,
  useConst,
} from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { router, usePage } from "@inertiajs/react";
import { motion } from "framer-motion";
import { Fragment, useCallback, useEffect, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";
import { LuTrash } from "react-icons/lu";

import Alert from "@/components/Alert";
import Autocomplete from "@/components/Autocomplete";
import CurrencyInput from "@/components/CurrencyInput";
import Input from "@/components/Input";
import Modal from "@/components/Modal";

import { DATE_FORMAT } from "@/constants/datetime";
import { ServiceMembershipType } from "@/constants/service";
import { UNITS } from "@/constants/unit";
import { VATS } from "@/constants/vat";

import { useGetInvoice } from "@/services/invoice";

import useAuthStore from "@/stores/auth";

import Invoice from "@/types/invoice";

import { hasPermission } from "@/utils/authorization";
import { getTranslatedOptions } from "@/utils/autocomplete";
import { formatCurrency } from "@/utils/currency";
import { toDayjs } from "@/utils/datetime";

import { PageProps } from "@/types";

import { InvoiceRow } from "./types";
import {
  createFixedPriceRows,
  createOrderHeaderRows,
  createSeparatorRow,
  getFixedPriceArticleIds,
  getInvoiceSummary,
  getNewRowSpecs,
} from "./utils";

type InvoiceRowPayload = {
  parentId: number;
  type: "fixed price" | "order";
  description: string;
  quantity: number;
  unit: string;
  price: number;
  discountPercentage: number;
  vat: number;
  hasRut: boolean;
  id?: number;
};

type FormValues = {
  rows: InvoiceRow[];
  sentAt: string;
  remark: string;
};

interface PreviewModalProps {
  transportArticleId: string;
  materialArticleId: string;
  isOpen: boolean;
  onClose: () => void;
  data?: Invoice;
}

const PreviewModal = ({
  data,
  transportArticleId,
  materialArticleId,
  isOpen,
  onClose,
}: PreviewModalProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;

  const language = useAuthStore((state) => state.language);
  const currency = useAuthStore((state) => state.currency);

  const [hoveredRowIndex, setHoveredRowIndex] = useState<number>();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const {
    register,
    reset,
    watch,
    setValue,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();

  const rows = watch("rows", []);
  const sentAt = watch("sentAt");

  const invoice = useGetInvoice(data?.id, {
    request: {
      include: [
        "user",
        "customer",
        "orders.rows",
        "orders.fixedPrice.rows",
        "orders.service",
        "orders.subscription.products.product",
        "orders.schedule.property.address.address",
      ],
      only: [
        "fortnoxInvoiceId",
        "type",
        "remark",
        "sentAt",
        "dueAt",
        "sentAt",
        "status",
        "user.fullname",
        "customer.fortnoxId",
        "customer.dueDays",
        "customer.invoiceMethod",
        "customer.membershipType",
        "orders.id",
        "orders.orderedAt",
        "orders.rows.id",
        "orders.rows.fortnoxArticleId",
        "orders.rows.description",
        "orders.rows.price",
        "orders.rows.quantity",
        "orders.rows.unit",
        "orders.rows.vat",
        "orders.rows.discountPercentage",
        "orders.rows.hasRut",
        "orders.fixedPrice.id",
        "orders.fixedPrice.isPerOrder",
        "orders.fixedPrice.rows.id",
        "orders.fixedPrice.rows.type",
        "orders.fixedPrice.rows.description",
        "orders.fixedPrice.rows.quantity",
        "orders.fixedPrice.rows.price",
        "orders.fixedPrice.rows.vatGroup",
        "orders.fixedPrice.rows.hasRut",
        "orders.service.fortnoxArticleId",
        "orders.subscription.products.product.fortnoxArticleId",
        "orders.schedule.property.address.address",
        "orders.schedule.property.id",
      ],
    },
  });

  const unitOptions = useConst(
    getTranslatedOptions(UNITS, (option) => ({
      label: `${t(option.label)} (${option.value})`,
      value: option.value,
    })),
  );

  const summary = getInvoiceSummary(rows, invoice.data);
  const isReadonly = invoice.data?.status !== "open";
  const dueAt =
    invoice.data && invoice.data.customer
      ? toDayjs(`${sentAt}T00:00:00Z`, false)
          .add(invoice.data.customer.dueDays, "day")
          .format(DATE_FORMAT)
      : "";

  const handleHoverRow = useCallback(
    (index: number) => {
      if (isReadonly) {
        setHoveredRowIndex(undefined);
        return;
      }

      const row = rows[index];

      if (row.isReadonly && !row.key.startsWith("#")) {
        setHoveredRowIndex(undefined);
        return;
      }

      setHoveredRowIndex(index);
    },
    [setHoveredRowIndex, rows, isReadonly],
  );

  const handleAddRow = useCallback(
    (index: number) => {
      const newRows = [...rows];
      const rowSpecs = getNewRowSpecs(newRows, index);

      const newRow: InvoiceRow = {
        key: crypto.randomUUID(),
        type: rowSpecs.type,
        parentId: rowSpecs.parentId,
        fortnoxArticleId: "",
        description: "",
        quantity: 0,
        unit: "",
        price: 0,
        discountPercentage: 0,
        vat: 0,
        hasRut: false,
        isReadonly,
        isHeader: false,
      };
      newRows.splice(index + 1, 0, newRow);
      setValue("rows", newRows);
      setHoveredRowIndex(undefined);
    },
    [rows, setValue, isReadonly],
  );

  const handleRemoveRow = useCallback(
    (index: number) => {
      const newRows = [...rows];
      newRows.splice(index, 1);
      setValue("rows", newRows);
    },
    [rows, setValue],
  );

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);

    const filteredRows = values.rows.reduce<InvoiceRowPayload[]>((acc, row) => {
      if (row.type === "separator" || row.isHeader || !row.parentId) {
        return acc;
      }

      acc.push({
        parentId: row.parentId,
        id: row.id,
        type: row.type,
        description: row.description,
        quantity: row.quantity,
        unit: row.unit,
        price: row.price,
        discountPercentage: row.discountPercentage,
        vat: row.vat,
        hasRut: row.hasRut,
      });

      return acc;
    }, []);

    router.patch(
      `/invoices/${data?.id}`,
      {
        rows: filteredRows,
        sentAt: toDayjs().format(`${values.sentAt}T00:00:00Z`),
        remark: values.remark,
      },
      {
        onFinish: () => setIsSubmitting(false),
        onSuccess: (page) => {
          const {
            flash: { error },
          } = (page as Page<PageProps>).props;

          if (error) {
            return;
          }
        },
      },
    );
  });

  useEffect(() => {
    if (!invoice.data || !invoice.data.orders || !isOpen) {
      // Reset rows when the modal is closed
      setValue("rows", []);
      return;
    }

    const includedMonthlyFixedPriceIds: number[] = [];
    const rows: InvoiceRow[] = [];
    let hasFixedPriceSeparator = false;

    // Display monthly fixed price rows
    for (const order of invoice.data.orders) {
      if (
        !order.fixedPrice ||
        order.fixedPrice.isPerOrder ||
        includedMonthlyFixedPriceIds.includes(order.fixedPrice.id)
      ) {
        continue;
      }

      hasFixedPriceSeparator = true;
      includedMonthlyFixedPriceIds.push(order.fixedPrice.id);

      rows.push(...createFixedPriceRows(t, order.fixedPrice, isReadonly));
    }

    for (let i = 0; i < invoice.data.orders.length; i++) {
      const order = invoice.data.orders[i];
      const orderRows: InvoiceRow[] = [];

      // Display per order fixed price rows
      if (order.fixedPrice && order.fixedPrice.isPerOrder) {
        orderRows.push(
          ...createFixedPriceRows(t, order.fixedPrice, isReadonly),
        );
      }

      const fixedPriceArticleIds = getFixedPriceArticleIds(
        order,
        transportArticleId,
        materialArticleId,
      );

      for (const row of order.rows ?? []) {
        if (
          order.fixedPrice &&
          fixedPriceArticleIds.includes(row.fortnoxArticleId)
        ) {
          continue;
        }

        orderRows.push({
          key: `${row.id}`,
          parentId: order.id,
          id: row.id,
          type: "order" as const,
          fortnoxArticleId: row.fortnoxArticleId,
          description: row.description,
          quantity: row.quantity,
          unit: row.unit,
          price: row.price,
          discountPercentage: row.discountPercentage ?? 0,
          vat: row.vat,
          hasRut: row.hasRut,
          isReadonly,
          isHeader: false,
        });
      }

      if (hasFixedPriceSeparator || i > 0) {
        rows.push(createSeparatorRow());
        hasFixedPriceSeparator = false;
      }

      rows.push(...createOrderHeaderRows(t, order));
      rows.push(...orderRows);
    }

    reset({
      rows,
      sentAt: toDayjs(invoice.data?.sentAt).format(DATE_FORMAT),
      remark: invoice.data?.remark,
    });
  }, [isOpen, invoice.data, isReadonly]);

  return (
    <Modal
      size="full"
      title={t("preview")}
      isOpen={isOpen}
      onClose={onClose}
      bodyContainer={{ px: 0, display: "flex", flexDirection: "column" }}
    >
      {invoice.isFetching || !invoice.data ? (
        <Flex
          direction="column"
          justify="center"
          align="center"
          flex={1}
          gap={4}
        >
          <Spinner size="lg" />
          <Text fontSize="sm">{t("please wait")}</Text>
        </Flex>
      ) : (
        <Flex direction="column" gap={12}>
          <Flex direction="column" gap={4} px={8}>
            <Alert
              status="info"
              title={t("info")}
              message={t("invoice edit info")}
              fontSize="small"
            />
          </Flex>
          <Flex direction="column" gap={4} px={8}>
            <Flex gap={4}>
              {invoice.data.fortnoxInvoiceId && (
                <Input
                  type="text"
                  labelText={t("fortnox invoice id")}
                  container={{ flex: 1 }}
                  defaultValue={invoice.data.fortnoxInvoiceId}
                  isReadOnly
                />
              )}
              <Input
                type="text"
                labelText={t("customer")}
                container={{ flex: 1 }}
                defaultValue={
                  invoice.data.customer?.fortnoxId
                    ? `${invoice.data.user?.fullname} (${invoice.data.customer.fortnoxId})`
                    : invoice.data.user?.fullname
                }
                isReadOnly
              />
              <Input
                type="text"
                labelText={t("send invoice method")}
                container={{ flex: 1 }}
                defaultValue={t(
                  invoice.data.customer?.invoiceMethod ?? "print",
                )}
                isReadOnly
              />
            </Flex>
            <Flex gap={4}>
              {isReadonly ? (
                <Input
                  type="date"
                  labelText={t("sent date")}
                  container={{ flex: 1 }}
                  defaultValue={toDayjs(invoice.data?.sentAt).format(
                    DATE_FORMAT,
                  )}
                  isReadOnly
                />
              ) : (
                <Input
                  type="date"
                  labelText={t("sent date")}
                  container={{ flex: 1 }}
                  errorText={errors.sentAt?.message || serverErrors.sentAt}
                  {...register("sentAt")}
                />
              )}
              <Input
                type="date"
                labelText={t("due date")}
                container={{ flex: 1 }}
                value={dueAt}
                isReadOnly
              />
              <Spacer />
            </Flex>
          </Flex>
          <Table
            size="sm"
            style={{
              borderCollapse: "separate",
              borderSpacing: 0,
              tableLayout: "fixed",
            }}
          >
            <colgroup>
              <col style={{ width: "100px" }} />
              <col style={{ minWidth: "300px" }} />
              <col style={{ width: "75px" }} />
              <col style={{ width: "125px" }} />
              <col style={{ width: "200px" }} />
              <col style={{ width: "150px" }} />
              <col style={{ width: "125px" }} />
              <col style={{ width: "200px" }} />
              <col style={{ width: "100px" }} />
              <col style={{ width: "75px" }} />
            </colgroup>
            <Thead>
              <Tr>
                <Th>{t("article no")}</Th>
                <Th>{t("name")}</Th>
                <Th textAlign="center">{t("rut")}</Th>
                <Th textAlign="right">{t("quantity")}</Th>
                <Th>{t("unit")}</Th>
                <Th textAlign="right">{t("price per unit")}</Th>
                <Th textAlign="right">{t("discount percentage")}</Th>
                <Th textAlign="right">{t("total")}</Th>
                <Th>{t("vat")}</Th>
                <Th />
              </Tr>
            </Thead>
            <Tbody onMouseLeave={() => setHoveredRowIndex(undefined)}>
              {rows.map((row, index) => (
                <Fragment key={`${row.type}-${row.key}-${index}`}>
                  <Tr
                    as={motion.tr}
                    bg={row.isReadonly ? "brand.50" : undefined}
                    _dark={{
                      bg: row.isReadonly ? "gray.800" : undefined,
                    }}
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    onMouseEnter={() => handleHoverRow(index)}
                  >
                    <Td fontSize="xs" h={10} boxSizing="content-box">
                      {row.fortnoxArticleId}
                    </Td>
                    <Td fontSize="xs">
                      {row.isReadonly ? (
                        row.description
                      ) : (
                        <Input
                          variant="unstyled"
                          inputContainer={{ p: 0 }}
                          fontSize="xs"
                          {...register(`rows.${index}.description`)}
                          onChange={(e) =>
                            setValue(
                              `rows.${index}.description`,
                              e.target.value,
                            )
                          }
                        />
                      )}
                    </Td>
                    <Td textAlign="center">
                      {row.isHeader ? null : (
                        <Checkbox
                          isChecked={row.hasRut}
                          {...register(`rows.${index}.hasRut`)}
                          isReadOnly={
                            row.isReadonly ||
                            invoice.data?.customer?.membershipType ===
                              ServiceMembershipType.COMPANY
                          }
                        />
                      )}
                    </Td>
                    <Td fontSize="xs" textAlign="right">
                      {row.isHeader ? null : row.isReadonly ? (
                        row.quantity
                      ) : (
                        <Input
                          variant="unstyled"
                          type="number"
                          inputContainer={{ p: 0 }}
                          fontSize="xs"
                          textAlign="right"
                          minW="unset"
                          min={0}
                          {...register(`rows.${index}.quantity`, {
                            setValueAs: (value) => Math.max(value, 0),
                          })}
                        />
                      )}
                    </Td>
                    <Td>
                      {row.isHeader ? null : row.isReadonly ? (
                        t(`unit.${row.unit}`) + ` (${row.unit})`
                      ) : (
                        <Autocomplete
                          variant="unstyled"
                          options={unitOptions}
                          inputContainer={{ p: 0 }}
                          w={100}
                          value={row.unit}
                          {...register(`rows.${index}.unit`)}
                          allowEmpty
                          stealthMode
                        />
                      )}
                    </Td>
                    <Td fontSize="xs" textAlign="right">
                      {row.isHeader ? null : row.isReadonly ? (
                        formatCurrency(language, currency, row.price)
                      ) : (
                        <CurrencyInput
                          variant="unstyled"
                          inputContainer={{ p: 0 }}
                          fontSize="xs"
                          textAlign="right"
                          language={language}
                          currency={currency}
                          value={row.price}
                          {...register(`rows.${index}.price`, {
                            valueAsNumber: true,
                          })}
                        />
                      )}
                    </Td>
                    <Td fontSize="xs" textAlign="right">
                      {row.isHeader ? null : row.isReadonly ||
                        row.type === "fixed price" ? (
                        row.discountPercentage
                      ) : (
                        <Input
                          variant="unstyled"
                          type="number"
                          inputContainer={{ p: 0 }}
                          fontSize="xs"
                          textAlign="right"
                          minW="unset"
                          min={0}
                          max={100}
                          {...register(`rows.${index}.discountPercentage`, {
                            setValueAs: (value) =>
                              Math.min(Math.max(value, 0), 100),
                          })}
                        />
                      )}
                    </Td>
                    <Td fontSize="xs" textAlign="right">
                      {!row.isReadonly || row.price
                        ? formatCurrency(
                            language,
                            currency,
                            row.price *
                              row.quantity *
                              (1 - row.discountPercentage / 100),
                          )
                        : ""}
                    </Td>
                    <Td fontSize="xs">
                      {row.isHeader ? null : row.isReadonly ? (
                        row.vat
                      ) : (
                        <Autocomplete
                          variant="unstyled"
                          options={VATS}
                          inputContainer={{ p: 0 }}
                          minW={50}
                          w={50}
                          value={row.vat}
                          {...register(`rows.${index}.vat`)}
                          stealthMode
                        />
                      )}
                    </Td>
                    <Td textAlign="center">
                      {!row.isReadonly && (
                        <Tooltip label="Remove Row">
                          <IconButton
                            variant="ghost"
                            size="sm"
                            aria-label="Remove Row"
                            colorScheme="red"
                            icon={<LuTrash />}
                            onClick={() => handleRemoveRow(index)}
                          />
                        </Tooltip>
                      )}
                    </Td>
                  </Tr>
                  {hoveredRowIndex === index && (
                    <Tr
                      as={motion.tr}
                      bg="gray.50"
                      _dark={{ bg: "gray.800" }}
                      initial={{ opacity: 0 }}
                      animate={{ opacity: 0.5 }}
                      exit={{ opacity: 0 }}
                    >
                      <Td
                        colSpan={10}
                        h={10}
                        boxSizing="content-box"
                        color="brand.500"
                        fontSize="xs"
                        fontWeight="bold"
                        textAlign="center"
                        cursor="pointer"
                        _dark={{
                          color: "brand.200",
                        }}
                        onClick={() => handleAddRow(index)}
                      >
                        {t(
                          `invoice preview new ${
                            getNewRowSpecs(rows, index).type
                          } row`,
                        )}
                      </Td>
                    </Tr>
                  )}
                </Fragment>
              ))}
            </Tbody>
          </Table>
          <Flex gap={4} px={8}>
            <Flex flex={1}>
              {isReadonly ? (
                <Input
                  as={Textarea}
                  labelText={t("invoice text")}
                  resize="none"
                  defaultValue={invoice.data?.remark}
                  isReadOnly
                />
              ) : (
                <Input
                  as={Textarea}
                  labelText={t("invoice text")}
                  resize="none"
                  {...register("remark")}
                />
              )}
            </Flex>
            <Spacer />
            <Flex direction="column" justify="space-between" flex={1.5} gap={4}>
              <Flex gap={4}>
                <Flex direction="column" flex={1} gap={2}>
                  <Text fontSize="xs" textAlign="right">
                    {t("gross")}
                  </Text>
                  <Text fontSize="small" fontWeight="bold" textAlign="right">
                    {formatCurrency(language, currency, summary.gross)}
                  </Text>
                </Flex>
                <Flex direction="column" flex={1} gap={2}>
                  <Text fontSize="xs" textAlign="right">
                    {t("net")}
                  </Text>
                  <Text fontSize="small" fontWeight="bold" textAlign="right">
                    {formatCurrency(language, currency, summary.net)}
                  </Text>
                </Flex>
                <Flex direction="column" flex={1} gap={2}>
                  <Text fontSize="xs" textAlign="right">
                    {t("tax reduction basis")}
                  </Text>
                  <Text fontSize="small" fontWeight="bold" textAlign="right">
                    {formatCurrency(
                      language,
                      currency,
                      summary.taxReductionBasis,
                    )}
                  </Text>
                </Flex>
                <Flex direction="column" flex={1} gap={2}>
                  <Text fontSize="xs" textAlign="right">
                    {t("total include vat")}
                  </Text>
                  <Text fontSize="small" fontWeight="bold" textAlign="right">
                    {formatCurrency(
                      language,
                      currency,
                      summary.totalIncludeVat,
                    )}
                  </Text>
                </Flex>
              </Flex>
              <Flex gap={4}>
                <Flex direction="column" flex={1} gap={2}>
                  <Text fontSize="xs" textAlign="right">
                    {t("round off")}
                  </Text>
                  <Text fontSize="small" fontWeight="bold" textAlign="right">
                    {formatCurrency(language, currency, summary.roundOff)}
                  </Text>
                </Flex>
                <Flex direction="column" flex={1} gap={2}>
                  <Text fontSize="xs" textAlign="right">
                    {t("vat")}
                  </Text>
                  <Text fontSize="small" fontWeight="bold" textAlign="right">
                    {formatCurrency(language, currency, summary.vat)}
                  </Text>
                </Flex>
                <Flex direction="column" flex={1} gap={2}>
                  <Text fontSize="xs" textAlign="right">
                    {t("tax reduction")}
                  </Text>
                  <Text fontSize="small" fontWeight="bold" textAlign="right">
                    {formatCurrency(language, currency, summary.taxReduction)}
                  </Text>
                </Flex>
                <Flex direction="column" flex={1} gap={2}>
                  <Text fontSize="xs" textAlign="right">
                    {t("total invoiced")}
                  </Text>
                  <Text
                    fontSize="small"
                    fontWeight="bold"
                    textAlign="right"
                    color="brand.500"
                    _dark={{ color: "brand.200" }}
                  >
                    {formatCurrency(language, currency, summary.totalInvoiced)}
                  </Text>
                </Flex>
              </Flex>
            </Flex>
          </Flex>
          {invoice.data?.status === "open" &&
            hasPermission("invoices update") && (
              <Flex justify="right" px={8} gap={4}>
                <Button colorScheme="gray" fontSize="sm" onClick={onClose}>
                  {t("close")}
                </Button>
                <Button
                  fontSize="sm"
                  isLoading={isSubmitting}
                  loadingText={t("please wait")}
                  onClick={handleSubmit}
                >
                  {t("save changes")}
                </Button>
              </Flex>
            )}
        </Flex>
      )}
    </Modal>
  );
};

export default PreviewModal;
