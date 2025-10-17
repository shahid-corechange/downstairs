import {
  Button,
  Flex,
  Heading,
  Icon,
  IconButton,
  Table,
  Tbody,
  Text,
  Th,
  Thead,
  Tooltip,
  Tr,
} from "@chakra-ui/react";
import { TinyColor } from "@ctrl/tinycolor";
import { usePage } from "@inertiajs/react";
import { useEffect, useRef } from "react";
import { useTranslation } from "react-i18next";
import { AiOutlineVerticalAlignTop } from "react-icons/ai";

import Empty from "@/components/Empty";

import { QUARTERS_IN_DAYS, SIMPLE_TIME_FORMAT } from "@/constants/datetime";
import { NAVBAR_HEIGHT, SCHEDULE_THEAD_HEIGHT } from "@/constants/layout";
import { DEFAULT_SUBSCRIPTION_REFILL_SEQUENCE } from "@/constants/subscription";

import type ScheduleType from "@/types/schedule";

import { convertWeeksToDays, toDayjs } from "@/utils/datetime";

import { PageProps } from "@/types";

import useScheduleStore from "../../store";
import { ScheduleOverviewPageProps } from "../../types";
import RescheduleConfirmation from "./components/RescheduleConfirmation";
import ScheduleDataBars from "./components/ScheduleDataBars";
import SchedulePlaceholder from "./components/SchedulePlaceholder";
import ScheduleRow from "./components/ScheduleRow";

const Schedule = () => {
  const { t } = useTranslation();
  const {
    defaultMinHourShow,
    defaultMaxHourShow,
    query,
    subscriptionRefillSequence,
  } = usePage<PageProps<ScheduleOverviewPageProps>>().props;
  const ref = useRef<HTMLDivElement>(null);
  const tableRef = useRef<HTMLTableElement>(null);

  const schedules = useScheduleStore((state) => state.schedules);
  const teams = useScheduleStore((state) => state.teams);
  const shownTeamIds = useScheduleStore((state) => state.shownTeamIds);
  const draggedSchedule = useScheduleStore((state) => state.draggedSchedule);
  const selectedDayIndex = useScheduleStore((state) => state.selectedDayIndex);
  const selectedDate = useScheduleStore((state) => state.selectedDate);
  const showEarlyHours = useScheduleStore((state) => state.showEarlyHours);
  const showLateHours = useScheduleStore((state) => state.showLateHours);
  const showWeekend = useScheduleStore((state) => state.showWeekend);
  const setCreationLimitDays = useScheduleStore(
    (state) => state.setCreationLimitDays,
  );
  const selectDayIndex = useScheduleStore((state) => state.selectDayIndex);
  const setScheduleComponentRef = useScheduleStore(
    (state) => state.setScheduleComponentRef,
  );
  const setTableComponentRef = useScheduleStore(
    (state) => state.setTableComponentRef,
  );
  const toggleShowEarlyHours = useScheduleStore(
    (state) => state.toggleShowEarlyHours,
  );

  const handleDateClick = (dayIndex: number) => {
    selectDayIndex(selectedDayIndex !== dayIndex ? dayIndex : null);
  };

  const getTextColor = (bgColor: string) => {
    const color = new TinyColor(bgColor);
    return color.isDark() ? "white" : "black";
  };

  const shouldShowRow = (time: string) => {
    return (
      (showEarlyHours && time < defaultMinHourShow) ||
      (time >= defaultMinHourShow && time < defaultMaxHourShow) ||
      (showLateHours && time >= defaultMaxHourShow)
    );
  };

  useEffect(() => {
    if (ref.current && tableRef.current && schedules.length > 0) {
      setScheduleComponentRef(ref);
      setTableComponentRef(tableRef);

      let earliestSchedule: ScheduleType | undefined = undefined;

      if (query?.scheduleId && Number(query.scheduleId) > 0) {
        earliestSchedule = schedules.find(
          (item) => item.id === Number(query.scheduleId),
        );
      }

      if (!earliestSchedule) {
        earliestSchedule = schedules.sort((a, b) => {
          const startAtTime = toDayjs(a.startAt).format(SIMPLE_TIME_FORMAT);
          const endAtTime = toDayjs(b.startAt).format(SIMPLE_TIME_FORMAT);
          return startAtTime > endAtTime ? 1 : -1;
        })[0];
      }

      const extractedFirstHour = toDayjs(earliestSchedule.startAt)
        .subtract(toDayjs(earliestSchedule.startAt).hour() > 0 ? 1 : 0, "hour")
        .format(SIMPLE_TIME_FORMAT);

      const barOffsetTop = tableRef.current?.querySelector<HTMLElement>(
        `td[data-time='${extractedFirstHour}']`,
      )?.offsetTop;

      setTimeout(() => {
        ref.current?.scrollTo({
          top: barOffsetTop ? barOffsetTop - SCHEDULE_THEAD_HEIGHT : 0,
          behavior: "smooth",
        });
      }, 1000);
    }
  }, [ref, query, schedules, tableRef]);

  useEffect(() => {
    const limitDays = convertWeeksToDays(
      subscriptionRefillSequence || DEFAULT_SUBSCRIPTION_REFILL_SEQUENCE,
    );
    setCreationLimitDays(limitDays);
  }, [subscriptionRefillSequence, convertWeeksToDays, setCreationLimitDays]);

  return (
    <Flex
      ref={ref}
      flex={1}
      direction="column"
      overflowX="scroll"
      height={`calc(100vh - ${NAVBAR_HEIGHT}px)`}
    >
      <Flex
        position="relative"
        flex={1}
        direction="column"
        justifyContent={shownTeamIds.length > 0 ? "flex-start" : "center"}
      >
        {shownTeamIds.length > 0 ? (
          <Table
            ref={tableRef}
            id="schedule-table"
            sx={{
              borderCollapse: "separate",
              borderSpacing: 0,
            }}
          >
            <Thead position="sticky" top={0} zIndex={3} boxShadow="md">
              <Tr>
                <Th
                  pos="sticky"
                  left={0}
                  w={16}
                  rowSpan={2}
                  borderBottom="1px"
                  borderBottomColor="brand.100"
                  borderRight="1px"
                  borderRightColor="inherit"
                  bg="white"
                  _dark={{ bg: "gray.800", borderBottomColor: "brand.700" }}
                  zIndex={2}
                  boxShadow="0px 0px 15px rgba(0, 0, 0, 0.2), 0px 0px 15px rgba(0, 0, 0, 0.06)"
                  clipPath="inset(0 -15px 0 0)"
                >
                  {t("week of year", { week: selectedDate.week() })}
                  <Tooltip
                    label={
                      showEarlyHours
                        ? t("hide early hours")
                        : t("show early hours")
                    }
                  >
                    <IconButton
                      variant="ghost"
                      position="absolute"
                      bottom={0}
                      left="50%"
                      transform="translateX(-50%)"
                      zIndex={3}
                      border="1px"
                      borderColor="inherit"
                      borderBottom="none"
                      borderBottomRadius={0}
                      boxShadow="md"
                      h={6}
                      minW={7}
                      aria-label={
                        showEarlyHours
                          ? t("hide early hours")
                          : t("show early hours")
                      }
                      onClick={toggleShowEarlyHours}
                    >
                      <Icon
                        as={AiOutlineVerticalAlignTop}
                        boxSize="5"
                        transform="auto"
                        rotate={showEarlyHours ? 180 : 0}
                        transition="transform 0.2s"
                      />
                    </IconButton>
                  </Tooltip>
                </Th>
                {teams.map(
                  (team) =>
                    shownTeamIds.includes(team.id) && (
                      <Th
                        key={team.id}
                        colSpan={showWeekend ? 7 : 5}
                        bg={team.color}
                        borderBottom="none"
                        borderRight="1px"
                        borderRightColor="gray.400"
                        textAlign="center"
                        color={getTextColor(team.color)}
                      >
                        {team.name}
                      </Th>
                    ),
                )}
              </Tr>
              <Tr>
                {teams.map(
                  (team) =>
                    shownTeamIds.includes(team.id) &&
                    [...Array(7).keys()].map((i) =>
                      !showWeekend && i >= 5 ? null : (
                        <Th
                          key={team.id + i}
                          borderRight="1px"
                          borderRightColor={i === 6 ? "gray.400" : "inherit"}
                          letterSpacing="tighter"
                          padding={0}
                          bg="white"
                          minW={8}
                          _dark={{ bg: "gray.800" }}
                        >
                          <Button
                            display="flex"
                            w="full"
                            variant="ghost"
                            bg={
                              selectedDayIndex === i ? "brand.100" : "inherit"
                            }
                            color={selectedDayIndex === i ? "black" : "inherit"}
                            flexDir="column"
                            justifyContent="center"
                            textAlign="center"
                            fontSize="smaller"
                            paddingX={1}
                            height={12}
                            minWidth={0}
                            fontWeight="medium"
                            rounded={0}
                            onClick={() => handleDateClick(i)}
                          >
                            <Text textTransform="capitalize">
                              {selectedDate.weekday(i).format("ddd")}
                            </Text>
                            <Text fontSize="small" fontWeight="semibold">
                              {selectedDate.weekday(i).format("DD")}
                            </Text>
                            <Text textTransform="capitalize">
                              {selectedDate.weekday(i).format("MMM")}
                            </Text>
                          </Button>
                        </Th>
                      ),
                    ),
                )}
              </Tr>
            </Thead>
            <Tbody>
              {QUARTERS_IN_DAYS.map(
                (time, i) =>
                  shouldShowRow(time) && (
                    <ScheduleRow
                      key={time}
                      time={time}
                      showTime={i % 4 === 0}
                      hasBorderBottom={i % 4 === 3}
                    />
                  ),
              )}
            </Tbody>
          </Table>
        ) : (
          <Empty
            px={8}
            imageProps={{
              h: { "base": 14, "xl": 16, "2xl": 20 },
              w: { "base": 24, "xl": 28, "2xl": 32 },
            }}
            description={
              <Flex direction="column" mt={2} gap={{ "base": 2, "2xl": 4 }}>
                <Heading
                  size={{ "base": "sm", "xl": "md", "2xl": "lg" }}
                  textAlign="center"
                  color="gray.500"
                >
                  {t("no schedules this week")}
                </Heading>
                <Text
                  fontSize={{
                    "base": "sm",
                    "xl": "md",
                    "2xl": "lg",
                  }}
                  align="center"
                  color="gray.500"
                >
                  {t("no schedules text")}
                </Text>
              </Flex>
            }
          />
        )}
        {draggedSchedule && <SchedulePlaceholder />}
        <ScheduleDataBars />
        <RescheduleConfirmation />
      </Flex>
    </Flex>
  );
};

export default Schedule;
