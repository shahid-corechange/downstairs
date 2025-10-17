import { Flex, useConst } from "@chakra-ui/react";
import { Head, router } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";
import { AiOutlineEye, AiOutlineFileText } from "react-icons/ai";
import { LuPlus, LuSendHorizonal, LuXCircle } from "react-icons/lu";

import AlertDialog from "@/components/AlertDialog";
import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { getInvoices } from "@/services/invoice";

import Invoice from "@/types/invoice";

import { hasPermission } from "@/utils/authorization";

import { PaginatedPageProps } from "@/types";

import getColumns from "./column";
import PreviewModal from "./components/PreviewModal";

type InvoiceProps = {
  invoices: Invoice[];
  transportArticleId: string;
  materialArticleId: string;
};

const InvoiceOverviewPage = ({
  invoices,
  sort,
  filter,
  pagination,
  transportArticleId,
  materialArticleId,
}: PaginatedPageProps<InvoiceProps>) => {
  const { t } = useTranslation();
  const { modal, modalData, openModal, closeModal } = usePageModal<
    Invoice,
    "preview" | "cancel" | "send"
  >();
  const [actionTitle, setActionTitle] = useState("");
  const [isLoading, setIsLoading] = useState(false);

  const columns = useConst(getColumns(t));

  const handleAction = () => {
    setIsLoading(true);

    router.post(`/invoices/${modalData?.id}/${modal}`, undefined, {
      onFinish: () => setIsLoading(false),
      onSuccess: closeModal,
    });
  };

  return (
    <>
      <Head>
        <title>{t("invoices")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("invoices")} />
        </Flex>

        <DataTable
          data={invoices}
          columns={columns}
          fetchFn={getInvoices}
          sort={sort}
          filters={filter.filters}
          orFilters={filter.orFilters}
          pagination={pagination}
          actions={[
            {
              label: t("preview"),
              icon: AiOutlineFileText,
              onClick: (row) => {
                openModal("preview", row.original);
              },
            },
            {
              label: t("orders"),
              icon: AiOutlineEye,
              isHidden: !hasPermission("orders index"),
              onClick: (row) => {
                window.open(
                  `/orders?invoiceId.eq=${row.original.id}`,
                  "_blank",
                );
              },
            },
            (row) =>
              row.original.status === "open"
                ? {
                    label: t("create in fortnox"),
                    icon: LuPlus,
                    colorScheme: "green",
                    color: "green.500",
                    _dark: { color: "green.200" },
                    isHidden: !hasPermission("invoices create fortnox"),
                    onClick: (row) => {
                      setActionTitle(t("create invoice in fortnox"));
                      openModal("create", row.original);
                    },
                  }
                : null,
            (row) =>
              row.original.status === "created"
                ? {
                    label: t("send"),
                    icon: LuSendHorizonal,
                    colorScheme: "green",
                    color: "green.500",
                    _dark: { color: "green.200" },
                    isHidden: !hasPermission("invoices send"),
                    onClick: (row) => {
                      setActionTitle(t("send invoice"));
                      openModal("send", row.original);
                    },
                  }
                : null,
            (row) =>
              row.original.status === "created"
                ? {
                    label: t("cancel"),
                    icon: LuXCircle,
                    colorScheme: "red",
                    color: "red.500",
                    _dark: { color: "red.200" },
                    order: 4,
                    isHidden: !hasPermission("invoices cancel"),
                    onClick: (row) => {
                      setActionTitle(t("cancel invoice"));
                      openModal("cancel", row.original);
                    },
                  }
                : null,
          ]}
          serverSide
          useWindowScroll
        />

        <PreviewModal
          data={modalData}
          isOpen={modal === "preview"}
          onClose={closeModal}
          transportArticleId={transportArticleId}
          materialArticleId={materialArticleId}
        />

        <AlertDialog
          title={actionTitle}
          confirmButton={{
            isLoading,
            colorScheme: modal === "cancel" ? "red" : "brand",
            loadingText: t("please wait"),
          }}
          confirmText={t("proceed")}
          isOpen={
            !!modalData && ["create", "cancel", "send"].includes(modal ?? "")
          }
          onClose={closeModal}
          onConfirm={handleAction}
        >
          {t("invoice action alert body", { action: modal })}
        </AlertDialog>
      </MainLayout>
    </>
  );
};

export default InvoiceOverviewPage;
