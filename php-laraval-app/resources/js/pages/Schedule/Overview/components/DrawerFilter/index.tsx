import {
  Avatar,
  Flex,
  Heading,
  Icon,
  IconButton,
  Spacer,
  Tooltip,
} from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import dayjs from "dayjs";
import { AnimatePresence, motion } from "framer-motion";
import { t } from "i18next";
import { useMemo, useState } from "react";
import {
  AiOutlineCalendar,
  AiOutlineExpandAlt,
  AiOutlineShrink,
} from "react-icons/ai";
import { LuCalendar, LuCalendarDays } from "react-icons/lu";
import { RiCloseLine, RiMenuFoldLine, RiTeamLine } from "react-icons/ri";

import Autocomplete from "@/components/Autocomplete";
import CalendarWeek from "@/components/CalendarWeek";
import HorizontalCollapse from "@/components/HorizontalCollapse";

import { DATE_FORMAT, SIMPLE_TIME_FORMAT } from "@/constants/datetime";
import { NAVBAR_HEIGHT } from "@/constants/layout";

import Schedule from "@/types/schedule";

import { renderHighlightedText } from "@/utils/autocomplete";
import { toDayjs } from "@/utils/datetime";
import { updateQueryString } from "@/utils/querystring";
import { createQueryString } from "@/utils/request";

import { PageProps } from "@/types";

import useScheduleStore from "../../store";
import { ScheduleOverviewPageProps } from "../../types";
import FilterForm from "../FilterForm";
import SearchForm from "../SearchForm";

const DrawerFilter = () => {
  const { defaultMinHourShow, defaultMaxHourShow } =
    usePage<PageProps<ScheduleOverviewPageProps>>().props;

  const [collapse, setCollapse] = useState(false);

  const schedules = useScheduleStore((state) => state.schedules);
  const teams = useScheduleStore((state) => state.teams);
  const shownTeamIds = useScheduleStore((state) => state.shownTeamIds);
  const selectedDate = useScheduleStore((state) => state.selectedDate);
  const showEarlyHours = useScheduleStore((state) => state.showEarlyHours);
  const showLateHours = useScheduleStore((state) => state.showLateHours);
  const showWeekend = useScheduleStore((state) => state.showWeekend);
  const selectDate = useScheduleStore((state) => state.selectDate);
  const addTeam = useScheduleStore((state) => state.addTeam);
  const removeTeam = useScheduleStore((state) => state.removeTeam);
  const setShownTeamIds = useScheduleStore((state) => state.setShownTeamIds);
  const setShowEarlyHours = useScheduleStore(
    (state) => state.setShowEarlyHours,
  );
  const setShowLateHours = useScheduleStore((state) => state.setShowLateHours);
  const setShowWeekend = useScheduleStore((state) => state.setShowWeekend);
  const toggleShowWeekend = useScheduleStore(
    (state) => state.toggleShowWeekend,
  );
  const toggleShowAll = useScheduleStore((state) => state.toggleShowAll);

  const showAllStatus = showEarlyHours && showLateHours && showWeekend;

  const teamOptions = useMemo(() => {
    const hiddenTeams = teams.filter((team) => !shownTeamIds.includes(team.id));
    return hiddenTeams.map((team) => ({ label: team.name, value: team.id }));
  }, [teams, shownTeamIds]);

  const handleChangeDate = (day: dayjs.Dayjs) => {
    selectDate(day);

    const startOfWeek = day.weekday(0).format(DATE_FORMAT);
    const endOfWeek = day.weekday(6).format(DATE_FORMAT);

    const qs = createQueryString<Schedule>({
      filter: {
        gte: { startAt: startOfWeek },
        lte: { endAt: endOfWeek },
      },
    });

    router.get(`/schedules${qs}`, undefined, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const handleChangeTeam = (teamId: number) => {
    updateQueryString("view.shownTeamIds", [...shownTeamIds, teamId].join(","));
    addTeam(teamId);
  };

  const handleShowTeamsWithBookings = () => {
    const teamIds: number[] = [];
    let shouldShowEarlyHours = false;
    let shouldShowLateHours = false;
    let shouldShowWeekend = false;

    for (const schedule of schedules) {
      if (schedule?.team?.id && !teamIds.includes(schedule.team.id)) {
        teamIds.push(schedule.team.id);
      }

      const startAt = toDayjs(schedule.startAt);
      const startHour = startAt.format(SIMPLE_TIME_FORMAT);

      if (startHour < defaultMinHourShow) {
        shouldShowEarlyHours = true;
      }

      if (startHour >= defaultMaxHourShow) {
        shouldShowLateHours = true;
      }

      if (startAt.weekday() >= 5) {
        shouldShowWeekend = true;
      }
    }

    updateQueryString("view.shownTeamIds", teamIds.join(","));
    updateQueryString("view.showEarlyHours", shouldShowEarlyHours.toString());
    updateQueryString("view.showLateHours", shouldShowLateHours.toString());
    updateQueryString("view.showWeekend", shouldShowWeekend.toString());
    setShownTeamIds(teamIds);
    setShowEarlyHours(shouldShowEarlyHours);
    setShowLateHours(shouldShowLateHours);
    setShowWeekend(shouldShowWeekend);
  };

  const handleShowAllTeams = () => {
    const allTeamIds = teams.map((team) => team.id);

    updateQueryString("view.shownTeamIds", allTeamIds.join(","));
    setShownTeamIds(allTeamIds);
  };

  const handleRemoveAllTeams = () => {
    updateQueryString("view.shownTeamIds", "");
    setShownTeamIds([]);
  };

  return (
    <Flex
      position="sticky"
      right={0}
      shrink={0}
      px={2}
      borderLeft="1px"
      borderColor="inherit"
      bg="var(--chakra-colors-chakra-body-bg)"
      zIndex={3}
    >
      <Flex py={6} direction="column" gap={4}>
        <Tooltip label={t("toggle filter menu")} placement="left">
          <IconButton
            variant="ghost"
            onClick={() => setCollapse(!collapse)}
            aria-label={t("toggle filter menu")}
            fontSize="xl"
          >
            <Icon
              as={RiMenuFoldLine}
              transition="all 0.2s"
              transform={`scaleX(${!collapse ? 1 : -1})`}
            />
          </IconButton>
        </Tooltip>
        <Tooltip
          label={showWeekend ? t("hide weekend") : t("show weekend")}
          placement="left"
        >
          <IconButton
            variant="ghost"
            aria-label={showWeekend ? t("hide weekend") : t("show weekend")}
            onClick={toggleShowWeekend}
            fontSize="xl"
          >
            <Icon as={showWeekend ? LuCalendar : LuCalendarDays} />
          </IconButton>
        </Tooltip>
        <Tooltip
          label={showAllStatus ? t("hide all") : t("show all")}
          placement="left"
        >
          <IconButton
            variant="ghost"
            aria-label={showAllStatus ? t("hide all") : t("show all")}
            onClick={toggleShowAll}
            fontSize="xl"
          >
            <Icon as={showAllStatus ? AiOutlineShrink : AiOutlineExpandAlt} />
          </IconButton>
        </Tooltip>
      </Flex>
      <HorizontalCollapse in={collapse}>
        <Flex
          direction="column"
          alignSelf="flex-start"
          gap={10}
          p={6}
          h={`calc(100vh - ${NAVBAR_HEIGHT}px)`}
          w="lg"
          overflow="auto"
        >
          <CalendarWeek
            size="xs"
            w="full"
            selectedDate={selectedDate}
            onChange={handleChangeDate}
          />

          <SearchForm />

          <FilterForm />

          <Flex direction="column" gap={6}>
            <Flex direction="row">
              <Flex align="center" gap={2} flex={1}>
                <Heading size="xs">{t("teams")}</Heading>
                <Flex
                  boxSize={6}
                  align="center"
                  justify="center"
                  bg="brand.100"
                  color="brand.800"
                  fontSize="small"
                  fontWeight="bold"
                  rounded="full"
                >
                  {shownTeamIds.length}
                </Flex>
              </Flex>
              <Flex gap={2}>
                {shownTeamIds.length !== teams.length && (
                  <Tooltip label={t("show teams with bookings")}>
                    <IconButton
                      variant="outline"
                      aria-label={t("show teams with bookings")}
                      size="xs"
                      onClick={handleShowTeamsWithBookings}
                      isRound
                    >
                      <Icon as={AiOutlineCalendar} fontSize="sm" />
                    </IconButton>
                  </Tooltip>
                )}
                {shownTeamIds.length !== teams.length && (
                  <Tooltip label={t("show all teams")}>
                    <IconButton
                      variant="outline"
                      aria-label={t("show all teams")}
                      size="xs"
                      isRound
                      onClick={handleShowAllTeams}
                    >
                      <Icon as={RiTeamLine} fontSize="sm" />
                    </IconButton>
                  </Tooltip>
                )}
                {shownTeamIds.length > 0 && (
                  <Tooltip label={t("hide all teams")}>
                    <IconButton
                      variant="outline"
                      aria-label={t("hide all teams")}
                      size="xs"
                      isRound
                      onClick={handleRemoveAllTeams}
                    >
                      <Icon as={RiCloseLine} fontSize="sm" />
                    </IconButton>
                  </Tooltip>
                )}
              </Flex>
            </Flex>

            <Autocomplete
              options={teamOptions}
              onChange={(e) => handleChangeTeam(Number(e.target.value))}
              renderOption={(option, filter) => {
                if (
                  typeof option === "string" ||
                  typeof option.value !== "number"
                ) {
                  return null;
                }

                const team = teams.find((team) => team.id === option.value);
                return (
                  team && (
                    <Flex flex={1} align="center">
                      <Avatar size="xs" src={team.avatar} mr={4} />
                      {renderHighlightedText(team.name, filter)}
                      <Spacer />
                      <Avatar size="2xs" ml={4} bg={team.color} name=" " />
                    </Flex>
                  )
                );
              }}
              clearOnSelect
            />

            <AnimatePresence>
              {teams.map(
                (team) =>
                  shownTeamIds.includes(team.id) && (
                    <Flex
                      as={motion.div}
                      key={team.id}
                      role="group"
                      align="center"
                      gap={4}
                      initial={{ opacity: 0, height: 0 }}
                      animate={{ opacity: 1, height: "auto" }}
                      exit={{ opacity: 0, height: 0 }}
                    >
                      <Avatar size="sm" src={team.avatar} />
                      <Heading size="xs" fontSize="small">
                        {team.name}
                      </Heading>
                      <Spacer />
                      <IconButton
                        variant="ghost"
                        size="xs"
                        aria-label={t("hide team")}
                        transition="opacity 0.2s"
                        opacity={0}
                        _groupHover={{ opacity: 1 }}
                        onClick={() => removeTeam(team.id)}
                        isRound
                      >
                        <Icon as={RiCloseLine} fontSize="md" />
                      </IconButton>
                      <Avatar size="2xs" bg={team.color} name=" " />
                    </Flex>
                  ),
              )}
            </AnimatePresence>
          </Flex>
        </Flex>
      </HorizontalCollapse>
    </Flex>
  );
};

export default DrawerFilter;
