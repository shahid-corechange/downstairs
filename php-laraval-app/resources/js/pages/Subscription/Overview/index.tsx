import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { AiOutlineEye } from "react-icons/ai";
import { TbCalendarBolt, TbCalendarPause } from "react-icons/tb";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import { getSubscriptions } from "@/services/subscription";

import Service from "@/types/service";
import Subscription from "@/types/subscription";
import Team from "@/types/team";

import { hasAnyPermissions, hasPermission } from "@/utils/authorization";

import { PageFilterItem, PaginatedPageProps } from "@/types";

import getColumns from "./column";
import ContinueModal from "./components/ContinueModal";
import DeleteModal from "./components/DeleteModal";
import EditModal from "./components/EditModal";
import PauseModal from "./components/PauseModal";
import RestoreModal from "./components/RestoreModal";
import ViewModal from "./components/ViewModal";

type SubscriptionProps = {
  subscriptions: Subscription[];
  frequencies: Record<string, string>;
  teams: Team[];
  services: Service[];
};

const defaultFilters: PageFilterItem[] = [
  {
    key: "deletedAt",
    criteria: "eq",
    value: false,
  },
];

const SubscriptionOverviewPage = ({
  subscriptions,
  frequencies,
  teams,
  services,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<SubscriptionProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } = usePageModal<
    Subscription,
    "view" | "pause" | "continue"
  >();
  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("subscriptions")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("subscriptions")} />
        </Flex>

        <DataTable
          data={subscriptions}
          columns={columns}
          title={t("subscriptions")}
          fetchFn={getSubscriptions}
          sort={sort}
          filters={[...defaultFilters, ...filter.filters]}
          orFilters={filter.orFilters}
          pagination={pagination}
          withEdit={(row) =>
            hasPermission("subscriptions update") &&
            !row.original.deletedAt &&
            row.original.subscribableType !==
              "App\\Models\\SubscriptionLaundryDetail"
          }
          withDelete={(row) =>
            hasPermission("subscriptions delete") &&
            !row.original.deletedAt &&
            row.original.subscribableType !==
              "App\\Models\\SubscriptionLaundryDetail"
          }
          withRestore={(row) =>
            hasPermission("subscriptions restore") &&
            row.original.subscribableType !==
              "App\\Models\\SubscriptionLaundryDetail"
          }
          onEdit={(row) => openModal("edit", row.original)}
          onDelete={(row) => openModal("delete", row.original)}
          onRestore={(row) => openModal("restore", row.original)}
          actions={[
            {
              label: t("view"),
              icon: AiOutlineEye,
              isHidden: (row) =>
                !hasAnyPermissions([
                  "subscriptions read",
                  "subscription tasks index",
                ]) ||
                !!row.original.deletedAt ||
                row.original.isPaused,
              onClick: (row) => {
                openModal("view", row.original);
              },
            },
            {
              label: t("pause"),
              icon: TbCalendarPause,
              colorScheme: "red",
              color: "red.500",
              _dark: { color: "red.200" },
              isHidden: (row) =>
                !hasPermission("subscriptions pause") ||
                !!row.original.deletedAt ||
                row.original.isPaused ||
                row.original.subscribableType ===
                  "App\\Models\\SubscriptionLaundryDetail",
              onClick: (row) => {
                openModal("pause", row.original);
              },
            },
            {
              label: t("continue"),
              icon: TbCalendarBolt,
              colorScheme: "green",
              color: "green.500",
              _dark: { color: "green.200" },
              isHidden: (row) =>
                !hasPermission("subscriptions continue") ||
                !row.original.isPaused ||
                row.original.subscribableType ===
                  "App\\Models\\SubscriptionLaundryDetail",
              onClick: (row) => {
                openModal("continue", row.original);
              },
            },
          ]}
          serverSide
          useWindowScroll
        />
      </MainLayout>
      <ViewModal
        subscriptionId={modalData?.id}
        isOpen={modal === "view"}
        onClose={closeModal}
      />
      <EditModal
        data={modalData}
        services={services}
        frequencies={frequencies}
        teams={teams}
        isOpen={modal === "edit"}
        onClose={closeModal}
      />
      <PauseModal
        data={modalData}
        isOpen={modal === "pause"}
        onClose={closeModal}
      />
      <ContinueModal
        data={modalData}
        isOpen={modal === "continue"}
        onClose={closeModal}
      />
      <DeleteModal
        data={modalData}
        isOpen={modal === "delete"}
        onClose={closeModal}
      />
      <RestoreModal
        data={modalData}
        isOpen={modal === "restore"}
        onClose={closeModal}
      />
    </>
  );
};

export default SubscriptionOverviewPage;
