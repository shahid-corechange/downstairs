import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { getUnassignSubscriptions } from "@/services/unassignSubscription";

import UnassignSubscription from "@/types/unassignSubscription";

import { hasPermission } from "@/utils/authorization";

import { PageProps } from "@/types";

import getColumns from "./column";
import CreateModal from "./components/CreateModal";
import DeleteModal from "./components/DeleteModal";
import EditModal from "./components/EditModal";
import { UnassignSubscriptionPageProps } from "./types";

const UnassignSubscriptionOverviewPage = ({
  unassignSubscriptions,
  sort,
}: PageProps<UnassignSubscriptionPageProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } =
    usePageModal<UnassignSubscription>();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("unassign subscriptions")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("unassign subscriptions")} />
        </Flex>

        <DataTable
          data={unassignSubscriptions}
          columns={columns}
          title={t("unassign subscriptions")}
          fetchFn={getUnassignSubscriptions}
          sort={sort}
          withCreate={hasPermission("unassign subscriptions create")}
          withEdit={() => hasPermission("unassign subscriptions update")}
          withDelete={() => hasPermission("unassign subscriptions delete")}
          onCreate={() => openModal("create")}
          onEdit={(row) => openModal("edit", row.original)}
          onDelete={(row) => openModal("delete", row.original)}
          useWindowScroll
          serverSide
        />
      </MainLayout>
      <CreateModal isOpen={modal === "create"} onClose={closeModal} />
      <EditModal
        data={modalData}
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

export default UnassignSubscriptionOverviewPage;
