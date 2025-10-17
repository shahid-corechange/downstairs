import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";
import { AiOutlineCheckCircle, AiOutlineCloseCircle } from "react-icons/ai";
import { RiExternalLinkLine } from "react-icons/ri";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { DATE_FORMAT } from "@/constants/datetime";

import MainLayout from "@/layouts/Main";

import { ScheduleChangeRequest } from "@/types/schedule";

import { hasPermission } from "@/utils/authorization";
import { toDayjs } from "@/utils/datetime";

import { PageProps } from "@/types";

import getColumns from "./column";
import ApproveModal from "./components/ApproveModal";
import RejectModal from "./components/RejectModal";

type ScheduleChangeRequestPageProps = {
  changeRequests: ScheduleChangeRequest[];
};

const ScheduleChangeRequestPage = ({
  changeRequests,
}: PageProps<ScheduleChangeRequestPageProps>) => {
  const { t } = useTranslation();

  const [selectedData, setSelectedData] = useState<ScheduleChangeRequest>();
  const [modal, setModal] = useState<"approve" | "reject">();

  const columns = useConst(getColumns(t));

  const handleAction = (
    data: ScheduleChangeRequest,
    action: "approve" | "reject",
  ) => {
    setSelectedData(data);
    setModal(action);
  };

  return (
    <>
      <Head>
        <title>{t("change requests")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("change requests")} />
        </Flex>

        <DataTable
          data={changeRequests}
          columns={columns}
          actions={[
            {
              label: t("view"),
              icon: RiExternalLinkLine,
              onClick: (row) => {
                const id = row.original.scheduleId;
                const startAt = toDayjs(row.original.schedule?.startAt);
                const startOfWeek = startAt.weekday(0).format(DATE_FORMAT);
                const endOfWeek = startAt.weekday(6).format(DATE_FORMAT);
                const shownTeamIds = row.original.schedule?.teamId;

                window.open(
                  `/schedules?scheduleId=${id}&startAt.gte=${startOfWeek}&endAt.lte=${endOfWeek}&view.shownTeamIds=${shownTeamIds}&view.showWeekend=true&view.showEarlyHours=true&view.showLateHours=true`,
                  "_blank",
                );
              },
            },
            {
              label: t("approve"),
              icon: AiOutlineCheckCircle,
              colorScheme: "green",
              color: "green.500",
              _dark: { color: "green.200" },
              isHidden: (row) =>
                !hasPermission("schedule change requests approve") ||
                !row.original.canReschedule,
              onClick: (row) => handleAction(row.original, "approve"),
            },
            {
              label: t("reject"),
              icon: AiOutlineCloseCircle,
              colorScheme: "red",
              color: "red.500",
              _dark: { color: "red.200" },
              isHidden: !hasPermission("schedule change requests reject"),
              onClick: (row) => handleAction(row.original, "reject"),
            },
          ]}
          useWindowScroll
        />
      </MainLayout>
      <ApproveModal
        data={selectedData}
        isOpen={modal === "approve"}
        onClose={() => setModal(undefined)}
      />
      <RejectModal
        data={selectedData}
        isOpen={modal === "reject"}
        onClose={() => setModal(undefined)}
      />
    </>
  );
};

export default ScheduleChangeRequestPage;
