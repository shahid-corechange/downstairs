import {
  Button,
  HStack,
  Icon,
  Popover,
  PopoverArrow,
  PopoverBody,
  PopoverCloseButton,
  PopoverContent,
  PopoverTrigger,
  useDisclosure,
} from "@chakra-ui/react";
import dayjs from "dayjs";
import React, { useState } from "react";
import DatePicker from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import { useTranslation } from "react-i18next";
import { AiOutlineCalendar } from "react-icons/ai";

import { DATE_FORMAT } from "@/constants/datetime";

import Input from "../Input";

export type DateRange = [Date, Date];
type DateRangeState = [Date | null, Date | null];

type DateRangePickerProps = {
  onChange?: (dates: DateRange) => void;
  defaultValue?: DateRangeState;
  submitButtonLabel?: string;
  minDate?: Date;
  maxDate?: Date;
};

const DateRangePicker: React.FC<DateRangePickerProps> = ({
  onChange,
  defaultValue,
  submitButtonLabel,
  minDate,
  maxDate,
}) => {
  const { t } = useTranslation();

  const [dateRange, setDateRange] = useState<DateRangeState>(
    defaultValue || [null, null],
  );
  const [startDate, endDate] = dateRange;

  const { onClose, onOpen, isOpen } = useDisclosure();

  const handleDateChange = (dates: DateRangeState) => {
    const [start, end] = dates;

    setDateRange(dates);

    if (start && end && !submitButtonLabel) {
      onChange?.([start, end]);
      onClose();
    }
  };

  const handleSubmit = () => {
    if (startDate && endDate) {
      onChange?.([startDate, endDate]);
      onClose();
    }
  };

  return (
    <Popover isOpen={isOpen} onOpen={onOpen} onClose={onClose}>
      <PopoverTrigger>
        <Input
          size="xs"
          container={{ width: "auto", flex: 0.5 }}
          suffix={<Icon as={AiOutlineCalendar} color="gray.500" />}
          placeholder={t("select date range")}
          value={
            startDate && endDate
              ? `${dayjs(startDate).format(DATE_FORMAT)} - ${dayjs(
                  endDate,
                ).format(DATE_FORMAT)}`
              : ""
          }
        />
      </PopoverTrigger>
      <PopoverContent inlineSize="lg" gap={4}>
        <PopoverArrow />
        <PopoverCloseButton />
        <PopoverBody gap={4}>
          <HStack mt={8} spacing={4} display="inline-block" align="start">
            <DatePicker
              selectsRange
              selected={endDate}
              onChange={handleDateChange}
              startDate={startDate || undefined}
              endDate={endDate || undefined}
              maxDate={maxDate}
              minDate={minDate}
              monthsShown={2}
              inline
            />
          </HStack>
          <HStack justifyContent="flex-end">
            <Button
              size="sm"
              color="blue.500"
              variant="ghost"
              onClick={() => {
                setDateRange([null, null]);
              }}
            >
              {t("clear")}
            </Button>
            {submitButtonLabel && (
              <Button
                size="sm"
                color="blue.500"
                variant="ghost"
                hidden={!startDate || !endDate}
                onClick={handleSubmit}
              >
                {submitButtonLabel}
              </Button>
            )}
          </HStack>
        </PopoverBody>
      </PopoverContent>
    </Popover>
  );
};

export default DateRangePicker;
