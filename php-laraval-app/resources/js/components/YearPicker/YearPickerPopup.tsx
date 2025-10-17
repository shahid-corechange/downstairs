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

import DecadePickerPopup from "./DecadePickerPopup";

interface YearPickerPopupProps {
  onSelect: (date: Dayjs) => void;
  onLevelChange?: () => void;
  date?: Dayjs;
}

const YearPickerPopup = ({
  onSelect,
  onLevelChange,
  date,
}: YearPickerPopupProps) => {
  const { t } = useTranslation();
  const [value, setValue] = useState(toDayjs(date, false).startOf("year"));
  const [level, setLevel] = useState<"year" | "decade">("year");

  const startOfDecade = value.year() - (value.year() % 10);

  const years = useMemo(() => {
    const selectedDate = toDayjs(date, false).startOf("year");

    return Array.from({ length: 12 }).map((_, index) => {
      const year = startOfDecade + index - 1; // -1 to start from the end of the previous decade
      const newDate = value.set("year", year);

      return (
        <AspectRatio
          key={year}
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
            color={index === 0 || index === 11 ? "gray.500" : undefined}
            isActive={selectedDate.year() === year}
            onClick={() => onSelect(newDate)}
          >
            {year}
          </Button>
        </AspectRatio>
      );
    });
  }, [date, value, startOfDecade, onSelect]);

  if (level === "decade") {
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
          onClick={() => (onLevelChange ? onLevelChange() : setLevel("decade"))}
        >
          {`${startOfDecade}-${startOfDecade + 9}`}
        </Button>
        <Spacer />
        <ButtonGroup variant="outline" size="xs" isAttached>
          <IconButton
            aria-label={t("previous decade")}
            onClick={() =>
              setValue((prev) => prev.set("year", startOfDecade - 10))
            }
          >
            <Icon as={LuChevronLeft} />
          </IconButton>
          <Button onClick={() => setValue(toDayjs().startOf("year"))}>
            {t("today")}
          </Button>
          <IconButton
            aria-label={t("next decade")}
            onClick={() =>
              setValue((prev) => prev.set("year", startOfDecade + 10))
            }
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
        {years}
      </SimpleGrid>
    </>
  );
};

export default YearPickerPopup;
