import { Flex, useConst } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";
import { RiExternalLinkLine } from "react-icons/ri";

import BrandText from "@/components/BrandText";
import Breadcrumb from "@/components/Breadcrumb";
import DataTable from "@/components/DataTable";

import { DATE_FORMAT } from "@/constants/datetime";

import MainLayout from "@/layouts/Main";

import { getChangeRequestHistories } from "@/services/changeRequest";

import { ScheduleChangeRequest } from "@/types/schedule";

import { toDayjs } from "@/utils/datetime";

import { PaginatedPageProps } from "@/types";

import getColumns from "./column";

type ScheduleChangeRequestHistoryPageProps = {
  changeRequests: ScheduleChangeRequest[];
};

const ScheduleChangeRequestHistoryPage = ({
  changeRequests,
  sort,
  filter,
  pagination,
}: PaginatedPageProps<ScheduleChangeRequestHistoryPageProps>) => {
  const { t } = useTranslation();

  const columns = useConst(getColumns(t));

  return (
    <>
      <Head>
        <title>{t("change requests histories")}</title>
      </Head>
      <MainLayout content={{ p: 6 }}>
        <Flex direction="column" mb={8}>
          <Breadcrumb />
          <BrandText text={t("change requests histories")} />
        </Flex>

        <DataTable
          data={changeRequests}
          columns={columns}
          fetchFn={getChangeRequestHistories}
          sort={sort}
          filters={filter.filters}
          orFilters={filter.orFilters}
          pagination={pagination}
          actions={[
            {
              label: t("view"),
              icon: RiExternalLinkLine,
              onClick: (row) => {
                const params = new URLSearchParams();
                const id = row.original.scheduleId;
                const startAt = row.original.schedule?.startAt;
                const shownTeamIds = row.original.schedule?.teamId;
                const status = row.original.schedule?.status;

                if (startAt) {
                  const startOfWeek = toDayjs(startAt)
                    .weekday(0)
                    .format(DATE_FORMAT);
                  const endOfWeek = toDayjs(startAt)
                    .weekday(6)
                    .format(DATE_FORMAT);

                  params.set("startAt.gte", startOfWeek);
                  params.set("endAt.lte", endOfWeek);
                }

                if (shownTeamIds) {
                  params.set("view.shownTeamIds", String(shownTeamIds));
                }

                if (status) {
                  params.set("view.statusFilter", status);
                }

                params.set("scheduleId", String(id));
                params.set("view.showWeekend", "true");
                params.set("view.showEarlyHours", "true");
                params.set("view.showLateHours", "true");

                window.open(`/schedules?${params.toString()}`, "_blank");
              },
            },
          ]}
          serverSide
          useWindowScroll
        />
      </MainLayout>
    </>
  );
};

export default ScheduleChangeRequestHistoryPage;
