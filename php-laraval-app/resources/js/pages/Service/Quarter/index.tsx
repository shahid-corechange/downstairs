import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import Service from "@/types/service";
import ServiceQuarter from "@/types/serviceQuarter";

import { hasPermission } from "@/utils/authorization";

import { PaginatedPageProps } from "@/types";

import getColumns from "./column";
import CreateModal from "./components/CreateModal";
import DeleteModal from "./components/DeleteModal";
import EditModal from "./components/EditModal";

export type QuarterProps = {
  serviceQuarters: ServiceQuarter[];
  services: Service[];
};

const QuartersOverviewPage = ({
  serviceQuarters,
  services,
}: PaginatedPageProps<QuarterProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<
    ServiceQuarter,
    "view"
  >();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("quarter rules")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("quarter rules")} />
        </Flex>
        <Alert
          status="info"
          title={t("info")}
          message={t("quarter rules info")}
          fontSize="small"
          mb={6}
        />
        <DataTable
          data={serviceQuarters}
          columns={columns}
          title={t("quarter rules")}
          withCreate={hasPermission("service quarters create")}
          withDelete={(row) =>
            hasPermission("service quarters delete") && !row.original.deletedAt
          }
          onCreate={() => openModal("create")}
          onEdit={(row) => openModal("edit", row.original)}
          onDelete={(row) => openModal("delete", row.original)}
          useWindowScroll
        />
      </MainLayout>
      <CreateModal
        services={services}
        isOpen={modal === "create"}
        onClose={closeModal}
      />
      <EditModal
        data={modalData}
        services={services}
        isOpen={modal === "edit"}
        onClose={closeModal}
      />
      <DeleteModal
        data={modalData}
        isOpen={modal === "delete"}
        onClose={closeModal}
      />
    </>
  );
};

export default QuartersOverviewPage;
