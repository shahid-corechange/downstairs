import { Flex } from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useEffect } from "react";
import { useTranslation } from "react-i18next";

import AuthorizationGuard from "@/components/AuthorizationGuard";

import MainLayout from "@/layouts/Main";

import { toDayjs } from "@/utils/datetime";
import { parseQueryString } from "@/utils/querystring";

import { PageProps } from "@/types";

import DrawerFilter from "./components/DrawerFilter";
import Schedule from "./components/Schedule";
import ScheduleModal from "./components/Schedule/components/ScheduleModal";
import useScheduleStore from "./store";
import { ScheduleFilterStatus, ScheduleOverviewPageProps } from "./types";

const ScheduleOverviewPage = ({
  schedules,
  teams,
  defaultShownTeamIds,
}: PageProps<ScheduleOverviewPageProps>) => {
  const { t } = useTranslation();

  const openedScheduleId = useScheduleStore((state) => state.openedScheduleId);
  const setShownTeamIds = useScheduleStore((state) => state.setShownTeamIds);
  const updateFilteredSchedule = useScheduleStore(
    (state) => state.updateFilteredSchedule,
  );
  const selectDate = useScheduleStore((state) => state.selectDate);
  const setSchedulesAndTeams = useScheduleStore(
    (state) => state.setSchedulesAndTeams,
  );
  const setOpenedScheduleId = useScheduleStore(
    (state) => state.setOpenedScheduleId,
  );
  const setShowWeekend = useScheduleStore((state) => state.setShowWeekend);
  const setShowEarlyHours = useScheduleStore(
    (state) => state.setShowEarlyHours,
  );
  const setShowLateHours = useScheduleStore((state) => state.setShowLateHours);
  const setStatusFilter = useScheduleStore((state) => state.setStatusFilter);
  const resetStore = useScheduleStore((state) => state.reset);

  useEffect(() => {
    const qs = parseQueryString();
    const startAt = qs["startAt.gte"];

    if (startAt) {
      const [year, month, date] = startAt.split("-").map(Number);
      const newDate = toDayjs()
        .year(year)
        .month(month - 1)
        .date(date)
        .startOf("day");
      selectDate(newDate);
    } else {
      selectDate(toDayjs().startOf("day"));
    }

    const shownTeamIds = qs["view.shownTeamIds"];
    setShownTeamIds(
      shownTeamIds !== undefined
        ? shownTeamIds.split(",").map(Number)
        : defaultShownTeamIds,
    );

    const showWeekend = qs["view.showWeekend"];
    setShowWeekend(showWeekend === "true");

    const showEarlyHours = qs["view.showEarlyHours"];
    setShowEarlyHours(showEarlyHours === "true");

    const showLateHours = qs["view.showLateHours"];
    setShowLateHours(showLateHours === "true");

    const statusFilter = qs["view.statusFilter"];
    setStatusFilter((statusFilter as ScheduleFilterStatus) || "active");

    return () => {
      resetStore();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    setSchedulesAndTeams({ schedules, teams });

    updateFilteredSchedule({});
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [schedules, teams]);

  return (
    <>
      <Head>
        <title>{t("schedules")}</title>
      </Head>
      <MainLayout>
        <Flex>
          <Schedule />
          <DrawerFilter />
        </Flex>
        <AuthorizationGuard
          permissions={[
            "schedules read",
            "schedule workers index",
            "schedule tasks index",
          ]}
        >
          <ScheduleModal
            isOpen={!!openedScheduleId}
            onClose={() => setOpenedScheduleId(undefined)}
            scheduleId={openedScheduleId || 0}
          />
        </AuthorizationGuard>
      </MainLayout>
    </>
  );
};

export default ScheduleOverviewPage;
