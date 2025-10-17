import { Button, Flex, FlexProps, Grid, Icon, Text } from "@chakra-ui/react";
import dayjs, { Dayjs } from "dayjs";
import { useEffect, useMemo, useState } from "react";
import { useTranslation } from "react-i18next";
import { LuChevronLeft, LuChevronRight } from "react-icons/lu";

import { DATE_FORMAT } from "@/constants/datetime";

import useAuthStore from "@/stores/auth";

import { transparentize } from "@/utils/color";
import { formatCurrency } from "@/utils/currency";

export type WeeklyDateOrder = {
  date: Dayjs;
  count: number;
  amount: number;
};

interface WeeklyDatePickerProps extends FlexProps {
  data?: WeeklyDateOrder[];
  initialDate?: Dayjs;
  isLoading?: boolean;
  onDateSelect?: (date: Dayjs) => void;
  onWeekChange?: (startDate: Dayjs, endDate: Dayjs) => void;
}

const todayColors = {
  button: {
    bg: "brand.200",
    _hover: {},
    _dark: {
      bg: transparentize("brand.200", 0.28),
      _hover: {
        bg: transparentize("brand.200", 0.28),
      },
    },
  },
  text: {
    color: "brand.500",
    _hover: {
      color: "brand.500",
    },
    _dark: {
      color: "brand.200",
      _hover: {
        color: "brand.200",
      },
    },
  },
};

const selectedDayColors = {
  button: {
    bg: "brand.400",
    _hover: {},
    _dark: {
      bg: "brand.200",
      _hover: {
        bg: "brand.200",
      },
    },
  },
  text: {
    color: "brand.50",
    _hover: {
      color: "brand.50",
    },
    _dark: {
      color: "brand.600",
      _hover: {
        color: "brand.600",
      },
    },
  },
};

const defaultColor = {
  button: {
    bg: "gray.100",
    _hover: {
      bg: "brand.100",
    },
    _dark: {
      bg: transparentize("brand.200", 0.12),
      _hover: {
        bg: transparentize("brand.200", 0.18),
      },
    },
  },
  text: {
    color: "brand.500",
    _dark: {
      color: "brand.200",
    },
  },
};

export default function WeeklyDatePicker({
  data = [],
  initialDate = dayjs(),
  isLoading = false,
  onDateSelect,
  onWeekChange,
  ...props
}: WeeklyDatePickerProps) {
  const { t } = useTranslation();

  const { currency, language } = useAuthStore.getState();

  const [startWeekDate, setStartWeekDate] = useState<Dayjs>(initialDate);
  const [selectedDate, setSelectedDate] = useState<Dayjs>(initialDate);
  const [action, setAction] = useState<"prev" | "next" | undefined>();

  const weekDays = useMemo(
    () =>
      Array.from({ length: 7 }, (_, i) => {
        const date = startWeekDate.startOf("week").add(i, "day");
        const dateStr = date.format(DATE_FORMAT);
        const dayName = date.format("dddd").toUpperCase();
        const dateDetails = data.find((item) =>
          item.date.isSame(date, "day"),
        ) || {
          count: 0,
          amount: 0,
        };

        return {
          date,
          dateStr,
          dayName,
          data: dateDetails,
        };
      }),
    [startWeekDate, data],
  );

  const handleWeekChange = (action: "prev" | "next") => {
    setAction(action);
    if (onWeekChange) {
      const newStartWeekDate = startWeekDate.add(
        action === "prev" ? -1 : 1,
        "week",
      );

      const startDate = newStartWeekDate.startOf("week");
      const endDate = startDate.endOf("week");
      onWeekChange(startDate, endDate);
    }
  };

  const handleDayClick = (date: Dayjs): void => {
    setSelectedDate(date);
    if (onDateSelect) {
      onDateSelect(date);
    }
  };

  const isToday = (date: Dayjs): boolean => {
    return date.isSame(dayjs(), "day");
  };

  const getColors = (isTodayDate: boolean, isSelectedDay: boolean) => {
    if (isTodayDate && isSelectedDay) {
      return selectedDayColors;
    } else if (isTodayDate) {
      return todayColors;
    } else if (isSelectedDay) {
      return selectedDayColors;
    }

    return defaultColor;
  };

  useEffect(() => {
    if (!isLoading && action) {
      if (action === "prev") {
        setStartWeekDate(startWeekDate.subtract(1, "week"));
      } else if (action === "next") {
        setStartWeekDate(startWeekDate.add(1, "week"));
      }
      setAction(undefined);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isLoading]);

  useEffect(() => {
    if (!initialDate.isValid()) {
      setStartWeekDate(dayjs());
      setSelectedDate(dayjs());
      return;
    }

    setStartWeekDate(initialDate);
    setSelectedDate(initialDate);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <Flex {...props} overflowX="auto" justifyContent="center">
      <Flex alignItems="center" gap={2}>
        {/* Previous week button */}
        <Button
          variant="solid"
          w={18}
          height={18}
          p={8}
          {...getColors(false, false).button}
          onClick={() => handleWeekChange("prev")}
          isLoading={isLoading && action === "prev"}
          aria-label={t("previous week")}
        >
          <Icon
            color="brand.500"
            _dark={{
              color: "brand.200",
            }}
            as={LuChevronLeft}
            boxSize={{ base: 6, xl: 8 }}
          />
        </Button>

        <Grid w="fit-content" templateColumns="repeat(7, 1fr)" gap={2}>
          {/* Days of the week */}
          {weekDays.map((day) => {
            const isTodayDate = isToday(day.date);
            const isSelectedDay =
              selectedDate?.isSame(day.date, "day") || false;

            return (
              <Button
                key={day.dateStr}
                variant="solid"
                {...getColors(isTodayDate, isSelectedDay).button}
                p={2}
                minW={{ base: 20, xl: 32 }}
                height={{ base: "auto", xl: 32 }}
                whiteSpace="nowrap"
                onClick={() => handleDayClick(day.date)}
                aria-label={`${t("select day", { day: day.dayName })}`}
                aria-selected={isSelectedDay}
                role="tab"
              >
                <Flex direction="column" alignItems="center">
                  <Text
                    fontSize={{ base: "x-small", xl: "xs" }}
                    {...getColors(isTodayDate, isSelectedDay).text}
                  >
                    {day.dateStr}
                  </Text>
                  <Text
                    fontSize={{ base: "x-small", xl: "small" }}
                    {...getColors(isTodayDate, isSelectedDay).text}
                  >
                    {day.dayName}
                  </Text>
                  <Text
                    fontSize={{ base: "large", xl: "xx-large" }}
                    fontWeight="bold"
                    my={2}
                    {...getColors(isTodayDate, isSelectedDay).text}
                  >
                    {day.data.count}
                  </Text>
                  <Text
                    fontSize={{ base: "xx-small", xl: "xs" }}
                    {...getColors(isTodayDate, isSelectedDay).text}
                  >
                    {t("total income for day", {
                      total: formatCurrency(
                        language,
                        currency,
                        day.data.amount,
                        0,
                      ),
                    })}
                  </Text>
                </Flex>
              </Button>
            );
          })}
        </Grid>

        {/* Next week button */}
        <Button
          variant="solid"
          w={18}
          height={18}
          p={8}
          {...getColors(false, false).button}
          onClick={() => handleWeekChange("next")}
          isLoading={isLoading && action === "next"}
          aria-label={t("next week")}
        >
          <Icon
            color="brand.500"
            _dark={{
              color: "brand.200",
            }}
            as={LuChevronRight}
            boxSize={{ base: 6, xl: 8 }}
          />
        </Button>
      </Flex>
    </Flex>
  );
}
