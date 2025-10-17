import { Icon, IconButton, Td, Tooltip, Tr } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import * as _ from "lodash-es";
import { memo, useMemo, useRef } from "react";
import { useTranslation } from "react-i18next";
import { AiOutlineVerticalAlignTop } from "react-icons/ai";

import { SIMPLE_TIME_FORMAT } from "@/constants/datetime";

import useScheduleStore from "@/pages/Schedule/Overview/store";
import { ScheduleOverviewPageProps } from "@/pages/Schedule/Overview/types";

import { toDayjs } from "@/utils/datetime";

import { PageProps } from "@/types";

import ScheduleCell from "../ScheduleCell";

interface ScheduleRowProps {
  time: string;
  showTime: boolean;
  hasBorderBottom: boolean;
}

const ScheduleRow = memo(
  ({ time, showTime, hasBorderBottom }: ScheduleRowProps) => {
    const { t } = useTranslation();
    const { defaultMaxHourShow } =
      usePage<PageProps<ScheduleOverviewPageProps>>().props;

    const teams = useScheduleStore((state) => state.teams);
    const shownTeamIds = useScheduleStore((state) => state.shownTeamIds);
    const showLateHours = useScheduleStore((state) => state.showLateHours);
    const showWeekend = useScheduleStore((state) => state.showWeekend);
    const toggleShowLateHours = useScheduleStore(
      (state) => state.toggleShowLateHours,
    );

    const ref = useRef<HTMLTableRowElement>(null);

    const timeToShowToggle = useMemo(
      () =>
        showLateHours
          ? "23:00"
          : toDayjs(toDayjs().format(`YYYY-MM-DDT${defaultMaxHourShow}:00Z`))
              .subtract(15, "minute")
              .startOf("hour")
              .format(SIMPLE_TIME_FORMAT),
      [defaultMaxHourShow, showLateHours],
    );

    return (
      <Tr ref={ref} overflowY="scroll">
        {showTime && (
          <Td
            rowSpan={4}
            data-time={time}
            py={0}
            px={2}
            borderRight="1px"
            borderColor="inherit"
            fontSize="small"
            fontWeight="bold"
            textAlign="center"
            bg="white"
            color="gray.500"
            verticalAlign="top"
            position="sticky"
            left={0}
            zIndex={2}
            boxShadow="0px 0px 15px rgba(0, 0, 0, 0.2), 0px 0px 15px rgba(0, 0, 0, 0.06)"
            clipPath="inset(0 -15px 0 0)"
            _dark={{ bg: "gray.800" }}
          >
            {showTime ? time : ""}
            {time === timeToShowToggle && (
              <Tooltip
                label={
                  showLateHours ? t("hide late hours") : t("show late hours")
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
                    showLateHours ? t("hide late hours") : t("show late hours")
                  }
                  onClick={toggleShowLateHours}
                >
                  <Icon
                    as={AiOutlineVerticalAlignTop}
                    boxSize="5"
                    transform="auto"
                    rotate={showLateHours ? 0 : 180}
                    transition="transform 0.2s"
                  />
                </IconButton>
              </Tooltip>
            )}
          </Td>
        )}

        {teams.map(
          (team) =>
            shownTeamIds.includes(team.id) &&
            [...Array(7).keys()].map((i) =>
              !showWeekend && i >= 5 ? null : (
                <ScheduleCell
                  key={`${team.id}-${i}-${time}`}
                  team={team}
                  dayIndex={i}
                  time={time}
                  borderBottom={hasBorderBottom ? undefined : "none"}
                />
              ),
            ),
        )}
      </Tr>
    );
  },
  (prev, next) => {
    return _.isEqual(prev, next);
  },
);

export default ScheduleRow;
