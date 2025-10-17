import {
  Button,
  ButtonGroup,
  Flex,
  FlexProps,
  Heading,
  Icon,
  IconButton,
  Spacer,
  Text,
  useConst,
} from "@chakra-ui/react";
import { Dayjs } from "dayjs";
import { useEffect, useMemo, useState } from "react";
import { useTranslation } from "react-i18next";
import { LuChevronLeft } from "react-icons/lu";

import { getWeeksOfMonth, toDayjs } from "@/utils/datetime";

const sizes = {
  xs: {
    width: "sm",
    buttonSize: "xs",
    fontSize: "small",
    gridButtonSize: 8,
    gridSpacingY: 2,
    gap: 2,
  },
  sm: {
    width: "sm",
    buttonSize: "sm",
    fontSize: "small",
    gridButtonSize: 10,
    gridSpacingY: 4,
    gap: 4,
  },
  md: {
    width: "md",
    buttonSize: "sm",
    fontSize: "sm",
    gridButtonSize: 10,
    gridSpacingY: 4,
    gap: 4,
  },
  lg: {
    width: "full",
    buttonSize: "md",
    fontSize: "md",
    gridButtonSize: 10,
    gridSpacingY: 4,
    gap: 4,
  },
};
export interface CalendarWeekProps extends Omit<FlexProps, "onChange"> {
  onChange: (date: Dayjs) => void;
  selectedDate?: Dayjs;
  size?: keyof typeof sizes;
  // blockdays?: BlockDay[];
}

const CalendarWeek = ({
  selectedDate,
  onChange,
  size = "md",
  // blockdays,
  ...props
}: CalendarWeekProps) => {
  const { t } = useTranslation();
  const today = useConst(toDayjs().startOf("day"));
  const [date, setDate] = useState(today);
  const weeksOfMonth = useMemo(() => getWeeksOfMonth(date), [date]);

  const weekIndex = useMemo(
    () =>
      selectedDate
        ? weeksOfMonth.findIndex((week) =>
            week.some((day) => day.isSame(selectedDate, "date")),
          )
        : -1,
    [selectedDate, weeksOfMonth],
  );

  useEffect(() => {
    if (selectedDate) {
      setDate(selectedDate);
    }
  }, [selectedDate]);

  return (
    <Flex direction="column" w={sizes[size].width} {...props}>
      <Flex align="center">
        <Heading size={size} textTransform="capitalize">
          {date.format("MMMM")}
        </Heading>
        <Heading size={size} color="gray.500" ml={1}>
          {date.format("YYYY")}
        </Heading>
        <Spacer />
        <ButtonGroup variant="outline" size={sizes[size].buttonSize} isAttached>
          <IconButton
            aria-label={t("previous month")}
            onClick={() => setDate((prev) => prev.subtract(1, "month"))}
          >
            <Icon as={LuChevronLeft} />
          </IconButton>
          <Button
            fontSize={sizes[size].fontSize}
            onClick={() => onChange(today)}
          >
            {t("today")}
          </Button>
          <IconButton
            aria-label={t("next month")}
            onClick={() => setDate((prev) => prev.add(1, "month"))}
          >
            <Icon as={LuChevronLeft} transform="scaleX(-1)" />
          </IconButton>
        </ButtonGroup>
      </Flex>
      <Flex flexDirection="column" gap={sizes[size].gap}>
        <Flex mt={8} mb={6} justifyContent="space-around">
          <Heading
            flex={1}
            textAlign="center"
            color="gray.500"
            fontSize={sizes[size].fontSize}
            textTransform="capitalize"
          >
            {t("week")}
          </Heading>
          {[...Array(7).keys()].map((i) => (
            <Heading
              key={i}
              flex={1}
              textAlign="center"
              color="gray.500"
              fontSize={sizes[size].fontSize}
              textTransform="capitalize"
            >
              {toDayjs().weekday(i).format("ddd")}
            </Heading>
          ))}
        </Flex>
        {weeksOfMonth.map((week, i) => {
          return (
            <Flex key={week[0].format()} alignItems="center">
              <Text
                flex={1 / 7}
                minW={0}
                p={0}
                fontSize={sizes[size].fontSize}
                color="inherit"
                textAlign="center"
                fontWeight="semibold"
              >
                {t("week of year", { week: week[0].week() })}
              </Text>
              <Button
                display="flex"
                flex={1}
                variant="ghost"
                px={0}
                rounded="full"
                border={weekIndex === i ? "1px" : undefined}
                onClick={() => onChange(week[0])}
              >
                {week.map((day) => (
                  <Text
                    key={day.format()}
                    flex={1}
                    minW={0}
                    p={0}
                    justifySelf="center"
                    fontSize={sizes[size].fontSize}
                    rounded="full"
                    color={day.isSame(date, "month") ? "inherit" : "gray.500"}
                  >
                    {day.format("D")}
                  </Text>
                ))}
              </Button>
            </Flex>
          );
        })}
      </Flex>
    </Flex>
  );
};

export default CalendarWeek;
