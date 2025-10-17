import {
  Box,
  Fade,
  Icon,
  IconButton,
  Popover,
  PopoverContent,
  Portal,
  useDisclosure,
  useId,
  useOutsideClick,
} from "@chakra-ui/react";
import { useSize } from "@chakra-ui/react-use-size";
import React, { forwardRef, useEffect, useMemo, useRef, useState } from "react";
import { LuChevronDown, LuX } from "react-icons/lu";

import Input, { InputProps } from "@/components/Input";

import { isDescendant } from "@/utils/dom";
import { dispatchInputChangeEvent } from "@/utils/event";

import AutocompleteOptions from "./components/AutocompleteOptions";
import { AutocompleteOption } from "./types";

export interface SingleAutocompleteProps extends Omit<InputProps, "multiple"> {
  options: (string | AutocompleteOption)[];
  allowEmpty?: boolean;
  clearOnSelect?: boolean;
  freeMode?: boolean;
  stealthMode?: boolean;
  isLoading?: boolean;
  customFilter?: string;
  renderOption?: (
    option: SingleAutocompleteProps["options"][number],
    filter?: string,
  ) => React.ReactNode;
  stickyFooterOption?: (onClose: () => void) => React.ReactNode;
}

const SingleAutocomplete = forwardRef<
  HTMLInputElement,
  SingleAutocompleteProps
>(
  (
    {
      options,
      name,
      value,
      allowEmpty,
      clearOnSelect,
      freeMode,
      stealthMode,
      isLoading,
      customFilter,
      isReadOnly,
      renderOption,
      onChange,
      onChangeDebounce,
      onBlur,
      size = "sm",
      prefix,
      stickyFooterOption,
      ...props
    },
    ref,
  ) => {
    const { isOpen, onOpen, onClose } = useDisclosure();
    const [selectedIndex, setSelectedIndex] = useState<number>();
    const [activeIndex, setActiveIndex] = useState<number>();
    const [filter, setFilter] = useState("");
    const [isContainerHovered, setIsContainerHovered] = useState(false);
    const containerRef = useRef<HTMLDivElement>(null);
    const inputGroupRef = useRef<HTMLDivElement>(null);
    const popoverRef = useRef<HTMLDivElement>(null);
    const containerSize = useSize(inputGroupRef);
    const inputId = useId(undefined, "autocomplete-input");

    const optionFilter = useMemo(() => {
      if (selectedIndex === undefined || !options[selectedIndex]) {
        return filter;
      }

      const option = options[selectedIndex];

      const optionLabel = typeof option === "string" ? option : option.label;

      return filter === optionLabel ? "" : filter;
    }, [filter, selectedIndex, options]);

    useOutsideClick({
      ref: popoverRef,
      enabled: isOpen,
      handler: () => {
        if (
          containerRef.current &&
          isDescendant(containerRef.current, document.activeElement)
        ) {
          return;
        }

        setActiveIndex(undefined);

        if (!freeMode) {
          const label = getSelectedOption("label");

          if (!label) {
            setFilter("");
            onClose();
            return;
          }

          setFilter(label);
        }

        onClose();
      },
    });

    const handleDispatchEvent = (
      label?: string,
      value?: string | number | boolean | readonly string[],
    ) => {
      const input = document.getElementById(inputId) as HTMLInputElement;

      if (!input) {
        return;
      }

      input.setAttribute("data-label", label ?? "");
      dispatchInputChangeEvent(input, value);
    };

    useEffect(() => {
      reset();

      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [options]);

    useEffect(() => {
      const index = options.findIndex((option) => {
        const optionValue = typeof option === "string" ? option : option.value;

        return `${value}` === `${optionValue}`;
      });

      setSelectedIndex(index >= 0 ? index : undefined);

      if (index >= 0) {
        const option = options[index];
        const optionLabel = typeof option === "string" ? option : option.label;

        setFilter(optionLabel);
      } else {
        setFilter(freeMode && value !== undefined ? `${value}` : "");
      }
    }, [freeMode, value, options]);

    useEffect(() => {
      setActiveIndex(undefined);
    }, [filter]);

    const toggleHoverContainer = () => {
      setIsContainerHovered((prev) => !prev);
    };

    const reset = (isDispatch: boolean = false) => {
      setSelectedIndex(undefined);
      setActiveIndex(undefined);
      setIsContainerHovered(false);

      if (!freeMode) {
        setFilter("");
      }

      if (isDispatch) {
        handleDispatchEvent();
      }
    };

    const getSelectedOption = (field: "label" | "value") => {
      if (selectedIndex === undefined || !options[selectedIndex]) {
        return "";
      }

      const option = options[selectedIndex];
      return typeof option === "string" ? option : `${option[field]}`;
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
      setFilter(e.target.value);

      if (freeMode) {
        handleDispatchEvent(e.target.value, e.target.value);
      }
    };

    const handleChangeDebounce = (
      value: string | number | readonly string[],
    ) => {
      setFilter(String(value));
      if (freeMode) {
        onChangeDebounce?.(String(value));
      }
    };

    const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
      if (e.key === "ArrowDown") {
        e.preventDefault();
        setActiveIndex((prev) => (prev === undefined ? 0 : prev + 1));

        const scroller =
          popoverRef.current?.firstElementChild?.firstElementChild;
        const list = scroller?.querySelector("ul");
        const option = list?.querySelector<HTMLLIElement>(
          `li[data-index='${activeIndex}']`,
        );

        if (scroller && list && option) {
          scroller.scrollTop =
            option.offsetTop + option.clientHeight * 2 - scroller.clientHeight;
        }
      } else if (e.key === "ArrowUp") {
        e.preventDefault();
        setActiveIndex((prev) => (prev && prev > 0 ? prev - 1 : undefined));

        const scroller =
          popoverRef.current?.firstElementChild?.firstElementChild;
        const list = scroller?.querySelector("ul");
        const option = list?.querySelector<HTMLLIElement>(
          `li[data-index='${activeIndex}']`,
        );

        if (scroller && list && option) {
          scroller.scrollTop =
            option.offsetTop > scroller.scrollTop
              ? scroller.scrollTop
              : option.offsetTop - option.clientHeight;
        }
      } else if (e.key === "Enter" && activeIndex !== undefined) {
        e.preventDefault();

        const option = popoverRef.current?.querySelector<HTMLLIElement>(
          `li[data-index='${activeIndex}']`,
        );

        if (option) {
          handleSelectOption(Number(option.getAttribute("data-optionindex")));
        }
      }
    };

    const handleSelectOption = (optionIndex: number) => {
      const option = options[optionIndex];
      const value = typeof option === "string" ? option : option.value;
      const label = typeof option === "string" ? option : option.label;

      handleDispatchEvent(label, value);

      onClose();

      if (clearOnSelect) {
        setSelectedIndex(undefined);
        setFilter("");
        return;
      }

      setSelectedIndex(optionIndex);
      setFilter(label);
    };

    return (
      <Popover isOpen={isOpen} initialFocusRef={containerRef}>
        <Box
          ref={containerRef}
          w="full"
          onFocus={isReadOnly ? undefined : onOpen}
          onMouseEnter={toggleHoverContainer}
          onMouseLeave={toggleHoverContainer}
        >
          <Input
            groupRef={inputGroupRef}
            size={size}
            prefix={prefix}
            suffix={
              stealthMode && !isContainerHovered ? undefined : selectedIndex !==
                  undefined &&
                allowEmpty &&
                isContainerHovered ? (
                <IconButton
                  size={size}
                  variant="ghost"
                  aria-label="Clear"
                  rounded="full"
                  onClick={() => reset(true)}
                >
                  <Icon as={LuX} />
                </IconButton>
              ) : (
                <Icon
                  as={LuChevronDown}
                  transition="transform 0.2s"
                  transform={`rotate(${isOpen ? "180deg" : "0deg"})`}
                />
              )
            }
            value={filter}
            isReadOnly={isReadOnly}
            onKeyDown={handleKeyDown}
            hasPopover
            {...(onChangeDebounce
              ? {
                  onChangeDebounce: handleChangeDebounce,
                }
              : {
                  onChange: handleChange,
                })}
            {...props}
          />
          <Input
            ref={ref}
            id={inputId}
            name={name}
            container={{ display: "none" }}
            value={freeMode ? filter : getSelectedOption("value")}
            onChange={onChange}
            onBlur={onBlur}
          />
        </Box>
        <Portal>
          <PopoverContent
            ref={popoverRef}
            w={containerSize?.width ? `${containerSize.width}px` : undefined}
          >
            <Fade in={isOpen} unmountOnExit>
              <AutocompleteOptions
                options={options}
                activeIndex={activeIndex}
                filter={customFilter ?? optionFilter}
                onChange={handleSelectOption}
                onClose={onClose}
                renderOption={renderOption}
                isLoading={isLoading}
                stickyFooterOption={stickyFooterOption}
              />
            </Fade>
          </PopoverContent>
        </Portal>
      </Popover>
    );
  },
);

export default SingleAutocomplete;
