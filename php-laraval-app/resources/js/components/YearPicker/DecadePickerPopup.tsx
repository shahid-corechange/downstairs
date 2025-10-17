import {
  AspectRatio,
  Button,
  ButtonGroup,
  Flex,
  Icon,
  IconButton,
  SimpleGrid,
  Spacer,
  Text,
} from "@chakra-ui/react";
import { Dayjs } from "dayjs";
import { useMemo, useState } from "react";
import { useTranslation } from "react-i18next";
import { LuChevronLeft } from "react-icons/lu";

import { toDayjs } from "@/utils/datetime";

interface DecadePickerPopupProps {
  onSelect: (date: Dayjs) => void;
  date?: Dayjs;
}

const DecadePickerPopup = ({ onSelect, date }: DecadePickerPopupProps) => {
  const { t } = useTranslation();
  const [value, setValue] = useState(toDayjs(date, false).startOf("year"));

  const startOfCentury = value.year() - (value.year() % 100);

  const years = useMemo(() => {
    const selectedDate = toDayjs(date, false).startOf("year");

    return Array.from({ length: 12 }).map((_, index) => {
      const year = startOfCentury + index * 10 - 10; // -10 to start from the end of the previous century
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
            isActive={
              selectedDate.year() >= year && selectedDate.year() <= year + 9
            }
            onClick={() => onSelect(newDate)}
          >
            {`${year}-${year + 9}`}
          </Button>
        </AspectRatio>
      );
    });
  }, [date, value, startOfCentury, onSelect]);

  return (
    <>
      <Flex alignItems="center" gap={4} mb={6}>
        <Button
          as={Text}
          variant="ghost"
          size="sm"
          userSelect="unset"
          _hover={{}}
        >{`${startOfCentury}-${startOfCentury + 99}`}</Button>
        <Spacer />
        <ButtonGroup variant="outline" size="xs" isAttached>
          <IconButton
            aria-label={t("previous century")}
            onClick={() =>
              setValue((prev) => prev.set("year", startOfCentury - 100))
            }
          >
            <Icon as={LuChevronLeft} />
          </IconButton>
          <Button onClick={() => setValue(toDayjs().startOf("year"))}>
            {t("today")}
          </Button>
          <IconButton
            aria-label={t("next century")}
            onClick={() =>
              setValue((prev) => prev.set("year", startOfCentury + 100))
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

export default DecadePickerPopup;
