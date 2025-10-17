import {
  AspectRatio,
  Button,
  ButtonGroup,
  Flex,
  Icon,
  IconButton,
  SimpleGrid,
  Spacer,
} from "@chakra-ui/react";
import { Dayjs } from "dayjs";
import { useMemo, useState } from "react";
import { useTranslation } from "react-i18next";
import { LuChevronLeft } from "react-icons/lu";

import { toDayjs } from "@/utils/datetime";

import DecadePickerPopup from "../YearPicker/DecadePickerPopup";
import YearPickerPopup from "../YearPicker/YearPickerPopup";

interface MonthPickerPopupProps {
  onSelect: (date: Dayjs) => void;
  onLevelChange?: () => void;
  date?: Dayjs;
}

const MonthPickerPopup = ({
  onSelect,
  onLevelChange,
  date,
}: MonthPickerPopupProps) => {
  const { t } = useTranslation();
  const [value, setValue] = useState(toDayjs(date, false).startOf("month"));
  const [level, setLevel] = useState<"month" | "year" | "decade">("month");

  const months = useMemo(() => {
    const selectedDate = toDayjs(date, false).startOf("month");

    return Array.from({ length: 12 }).map((_, index) => {
      const month = value.set("month", index);
      const monthName = month.format("MMM");

      return (
        <AspectRatio
          key={monthName}
          ratio={2 / 1}
          borderTop={index < 3 ? 0 : 1}
          borderRight={index % 3 === 2 ? 0 : 1}
          borderStyle="solid"
          borderColor="inherit"
        >
          <Button
            variant="ghost"
            size="sm"
            fontSize="xs"
            textTransform="capitalize"
            rounded="none"
            isActive={
              selectedDate.format("YYYY-MM") === month.format("YYYY-MM")
            }
            onClick={() => onSelect(month)}
          >
            {monthName}
          </Button>
        </AspectRatio>
      );
    });
  }, [date, value, onSelect]);

  if (level === "year") {
    return (
      <YearPickerPopup
        onSelect={(newDate) => {
          setValue(newDate.startOf("month"));
          setLevel("month");
        }}
        onLevelChange={() => setLevel("decade")}
        date={value}
      />
    );
  } else if (level === "decade") {
    return (
      <DecadePickerPopup
        onSelect={(newDate) => {
          setValue(newDate.startOf("year"));
          setLevel("year");
        }}
        date={value}
      />
    );
  }

  return (
    <>
      <Flex alignItems="center" gap={4} mb={6}>
        <Button
          variant="ghost"
          size="sm"
          justifyContent="flex-start"
          minW="fit-content"
          onClick={() => (onLevelChange ? onLevelChange() : setLevel("year"))}
        >
          {value.format("YYYY")}
        </Button>
        <Spacer />
        <ButtonGroup variant="outline" size="xs" isAttached>
          <IconButton
            aria-label={t("previous year")}
            onClick={() => setValue((prev) => prev.subtract(1, "year"))}
          >
            <Icon as={LuChevronLeft} />
          </IconButton>
          <Button onClick={() => setValue(toDayjs().startOf("month"))}>
            {t("today")}
          </Button>
          <IconButton
            aria-label={t("next year")}
            onClick={() => setValue((prev) => prev.add(1, "year"))}
          >
            <Icon as={LuChevronLeft} transform="scaleX(-1)" />
          </IconButton>
        </ButtonGroup>
      </Flex>
      <SimpleGrid
        columns={3}
        border={1}
        borderStyle="solid"
        borderColor="inherit"
        borderRadius="md"
        overflow="hidden"
      >
        {months}
      </SimpleGrid>
    </>
  );
};

export default MonthPickerPopup;
