import {
  Button,
  ButtonGroup,
  Flex,
  FlexProps,
  Heading,
  Icon,
  IconButton,
  SimpleGrid,
  Spacer,
  Text,
  useConst,
} from "@chakra-ui/react";
import { Dayjs } from "dayjs";
import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { LuChevronLeft } from "react-icons/lu";

import { DATE_FORMAT, SHORT_WEEKDAYS } from "@/constants/datetime";

import { BlockDay } from "@/types/blockday";

import { toDayjs } from "@/utils/datetime";

const sizes = {
  xs: {
    width: "sm",
    buttonSize: "xs",
    fontSize: "small",
    gridButtonSize: 8,
    gridSpacingY: 2,
  },
  sm: {
    width: "sm",
    buttonSize: "sm",
    fontSize: "small",
    gridButtonSize: 10,
    gridSpacingY: 4,
  },
  md: {
    width: "md",
    buttonSize: "sm",
    fontSize: "sm",
    gridButtonSize: 10,
    gridSpacingY: 4,
  },
  lg: {
    width: "full",
    buttonSize: "md",
    fontSize: "md",
    gridButtonSize: 10,
    gridSpacingY: 4,
  },
};

export interface CalendarProps extends Omit<FlexProps, "onChange"> {
  selectedDate?: Dayjs;
  size?: keyof typeof sizes;
  onChange?: (date: Dayjs) => void;
  blockdays?: BlockDay[];
}

const Calendar = ({
  selectedDate,
  onChange,
  size = "md",
  blockdays,
  ...props
}: CalendarProps) => {
  const { t } = useTranslation();
  const today = useConst(toDayjs().startOf("day"));

  const [date, setDate] = useState(today);
  const startDayIndex = date.startOf("month").weekday();
  const endDayIndex = date.endOf("month").weekday();

  const handleChange = (newDate: Dayjs) => {
    if (onChange) {
      return onChange(newDate);
    }

    setDate(newDate);
  };

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
            onClick={() => handleChange(today)}
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
      <SimpleGrid columns={7} mt={8} spacingY={sizes[size].gridSpacingY}>
        {SHORT_WEEKDAYS.map((day) => (
          <Heading
            key={day}
            textAlign="center"
            color="gray.500"
            fontSize={sizes[size].fontSize}
          >
            {t(day)}
          </Heading>
        ))}
      </SimpleGrid>
      <SimpleGrid
        columns={7}
        mt={6}
        alignItems="center"
        spacingY={sizes[size].gridSpacingY}
      >
        {Array.from({ length: startDayIndex }).map((_, index) => (
          <Text
            key={index}
            textAlign="center"
            color="gray.500"
            fontSize={sizes[size].fontSize}
            fontWeight="bold"
            opacity={0.5}
          >
            {date
              .startOf("month")
              .subtract(startDayIndex - index, "day")
              .date()}
          </Text>
        ))}
        {Array.from({ length: date.daysInMonth() }).map((_, index) =>
          onChange ? (
            <Button
              key={index}
              variant={
                date.set("date", index + 1).isSame(selectedDate, "date")
                  ? "outline"
                  : "ghost"
              }
              boxSize={sizes[size].gridButtonSize}
              minW={0}
              p={0}
              color={
                blockdays?.some(
                  (obj) =>
                    date.set("date", index + 1).format(DATE_FORMAT) ===
                    obj.blockDate,
                )
                  ? "red.500"
                  : undefined
              }
              justifySelf="center"
              fontSize={sizes[size].fontSize}
              rounded="full"
              onClick={() =>
                handleChange(
                  date
                    .set("date", index + 1)
                    .tz()
                    .set("date", index + 1)
                    .startOf("day"),
                )
              }
            >
              {index + 1}
            </Button>
          ) : (
            <Text
              key={index}
              textAlign="center"
              fontSize={sizes[size].fontSize}
              fontWeight="bold"
            >
              {index + 1}
            </Text>
          ),
        )}
        {Array.from({
          /*
            35 = 7 days * 5 weeks
            13 = 2 weeks index
            6 = 1 week index
          */
          length:
            (date.daysInMonth() + startDayIndex <= 35 ? 13 : 6) - endDayIndex,
        }).map((_, index) => (
          <Text
            key={index}
            textAlign="center"
            color="gray.500"
            fontSize={sizes[size].fontSize}
            fontWeight="bold"
            opacity={0.5}
          >
            {date
              .endOf("month")
              .add(index + 1, "day")
              .date()}
          </Text>
        ))}
      </SimpleGrid>
    </Flex>
  );
};

export default Calendar;
