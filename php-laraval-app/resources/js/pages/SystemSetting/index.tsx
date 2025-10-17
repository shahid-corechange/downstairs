import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { usePageModal } from "@/hooks/modal";

import MainLayout from "@/layouts/Main";

import GlobalSetting from "@/types/globalSetting";

import { hasPermission } from "@/utils/authorization";

import { PageProps } from "@/types";

import getColumns from "./column";
import EditModal from "./components/EditModal";
import { SettingProps } from "./types";

const ApplicationSettingverviewPage = ({
  settings,
  teams,
  refillSequences,
}: PageProps<SettingProps>) => {
  const { t } = useTranslation();

  const { modal, modalData, openModal, closeModal } =
    usePageModal<GlobalSetting>();

  const columns = useConst(getColumns(t, teams, refillSequences));

  return (
    <>
      <Head>
        <title>{t("system settings")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("system settings")} />
        </Flex>
        <Alert
          status="info"
          title={t("info")}
          message={t("system settings info")}
          fontSize="small"
          mb={6}
        />
        <DataTable
          data={settings}
          columns={columns}
          title={t("system settings")}
          withEdit={hasPermission("system settings update")}
          onEdit={(row) => openModal("edit", row.original)}
          useWindowScroll
        />
      </MainLayout>
      <EditModal
        data={modalData}
        refillSequences={refillSequences}
        isOpen={modal === "edit"}
        onClose={closeModal}
      />
    </>
  );
};

export default ApplicationSettingverviewPage;
