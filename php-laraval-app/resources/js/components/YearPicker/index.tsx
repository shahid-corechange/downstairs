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
import YearPickerPopup from "./YearPickerPopup";

interface YearPickerProps extends Omit<InputProps, "suffix" | "value"> {
  value?: string;
  popoverContainer?: PopoverProps;
  popoverContent?: PopoverContentProps;
  popoverBody?: PopoverBodyProps;
}

const YearPicker = forwardRef<HTMLInputElement, YearPickerProps>(
  ({ popoverContainer, popoverContent, popoverBody, ...props }, ref) => {
    const { isOpen, onOpen, onClose } = useDisclosure();
    const inputRef = useRef<HTMLInputElement>(null);
    const popoverRef = useRef<HTMLDivElement>(null);

    const value = props.value ? toDayjs(props.value, false) : undefined;

    const handleSelect = useCallback(
      (date: Dayjs) => {
        if (inputRef.current) {
          dispatchInputChangeEvent(inputRef.current, date.format("YYYY"));
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
            value={value?.format("YYYY")}
            onClick={onOpen}
            paddingOnInput
          />
        </PopoverAnchor>
        <Portal>
          <PopoverContent {...popoverContent} ref={popoverRef} my={4}>
            <PopoverBody {...popoverBody} p={4}>
              {isOpen && (
                <YearPickerPopup date={value} onSelect={handleSelect} />
              )}
            </PopoverBody>
          </PopoverContent>
        </Portal>
      </Popover>
    );
  },
);

export default YearPicker;
