import {
  Icon,
  Popover,
  PopoverAnchor,
  PopoverBody,
  PopoverBodyProps,
  PopoverContent,
  PopoverContentProps,
  PopoverProps,
  Portal,
  useDisclosure,
  useOutsideClick,
} from "@chakra-ui/react";
import { Dayjs } from "dayjs";
import { forwardRef, useCallback, useImperativeHandle, useRef } from "react";
import { AiOutlineCalendar } from "react-icons/ai";

import { toDayjs } from "@/utils/datetime";
import { dispatchInputChangeEvent } from "@/utils/event";

import Input, { InputProps } from "../Input";
import MonthPickerPopup from "./PickerPopup";

interface MonthPickerProps extends Omit<InputProps, "suffix" | "value"> {
  value?: string;
  popoverContainer?: PopoverProps;
  popoverContent?: PopoverContentProps;
  popoverBody?: PopoverBodyProps;
}

const MonthPicker = forwardRef<HTMLInputElement, MonthPickerProps>(
  ({ popoverContainer, popoverContent, popoverBody, ...props }, ref) => {
    const { isOpen, onOpen, onClose } = useDisclosure();
    const inputRef = useRef<HTMLInputElement>(null);
    const popoverRef = useRef<HTMLDivElement>(null);

    const value = props.value ? toDayjs(props.value, false) : undefined;

    const handleSelect = useCallback(
      (date: Dayjs) => {
        if (inputRef.current) {
          dispatchInputChangeEvent(inputRef.current, date.format("YYYY-MM"));
        }
        onClose();
      },
      [inputRef.current],
    );

    useImperativeHandle<HTMLInputElement | null, HTMLInputElement | null>(
      ref,
      () => inputRef.current,
      [inputRef],
    );

    useOutsideClick({
      ref: popoverRef,
      handler: onClose,
    });

    return (
      <Popover
        placement="bottom-start"
        {...popoverContainer}
        isOpen={isOpen}
        onClose={onClose}
      >
        <PopoverAnchor>
          <Input
            {...props}
            ref={inputRef}
            suffix={<Icon as={AiOutlineCalendar} />}
            cursor="pointer"
            value={value?.format("YYYY-MM")}
            onClick={onOpen}
            paddingOnInput
          />
        </PopoverAnchor>
        <Portal>
          <PopoverContent {...popoverContent} ref={popoverRef} my={4}>
            <PopoverBody {...popoverBody} p={4}>
              {isOpen && (
                <MonthPickerPopup date={value} onSelect={handleSelect} />
              )}
            </PopoverBody>
          </PopoverContent>
        </Portal>
      </Popover>
    );
  },
);

export default MonthPicker;
