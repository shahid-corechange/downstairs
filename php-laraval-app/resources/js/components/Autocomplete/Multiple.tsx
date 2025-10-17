import {
  Box,
  Fade,
  Icon,
  Popover,
  PopoverContent,
  Portal,
  Tag,
  TagCloseButton,
  TagLabel,
  useDisclosure,
  useId,
  useOutsideClick,
} from "@chakra-ui/react";
import { useSize } from "@chakra-ui/react-use-size";
import { forwardRef, useEffect, useMemo, useRef, useState } from "react";
import { Trans } from "react-i18next";
import { LuChevronDown } from "react-icons/lu";

import Input, { InputProps } from "@/components/Input";

import { isDescendant } from "@/utils/dom";
import { dispatchInputChangeEvent } from "@/utils/event";

import AutocompleteOptions from "./components/AutocompleteOptions";
import { AutocompleteOption } from "./types";

export interface MultipleAutocompleteProps
  extends Omit<InputProps, "multiple"> {
  options: (string | AutocompleteOption)[];
  renderOption?: (
    option: MultipleAutocompleteProps["options"][number],
    filter?: string,
  ) => React.ReactNode;
  placeholderTags?: string[];
  freeMode?: boolean;
  isLoading?: boolean;
  maxTags?: number;
}

const MultipleAutocomplete = forwardRef<
  HTMLInputElement,
  MultipleAutocompleteProps
>(
  (
    {
      options,
      name,
      value,
      placeholderTags,
      freeMode,
      isLoading,
      isReadOnly,
      maxTags,
      renderOption,
      onChange,
      onBlur,
      ...props
    },
    ref,
  ) => {
    const { isOpen, onOpen, onClose } = useDisclosure();
    const [selectedValues, setSelectedValues] = useState<
      AutocompleteOption["value"][]
    >([]);
    const [activeIndex, setActiveIndex] = useState<number>();
    const [filter, setFilter] = useState("");
    const [collapsed, setCollapsed] = useState(maxTags && true);
    const containerRef = useRef<HTMLDivElement>(null);
    const popoverRef = useRef<HTMLDivElement>(null);
    const containerSize = useSize(containerRef);
    const inputId = useId(undefined, "autocomplete-input");

    const tags = useMemo(() => {
      return selectedValues.map((value) => {
        const option = options.find((item) =>
          typeof item === "string" ? item === value : item.value === value,
        );
        return !option || typeof option === "string"
          ? (value as string)
          : option.label;
      });
    }, [options, selectedValues]);

    const filteredOptions = useMemo(() => {
      return options.filter((option) => {
        return (
          selectedValues.findIndex((value) =>
            typeof option === "string"
              ? value === option
              : value === option.value,
          ) === -1
        );
      });
    }, [options, selectedValues]);

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
        onClose();
        setCollapsed(true);
      },
    });

    const handleDispatchEvent = (values: (string | number | boolean)[]) => {
      const input = document.getElementById(inputId) as HTMLInputElement;

      if (!input) {
        return;
      }

      dispatchInputChangeEvent(
        input,
        values.length === 0 ? "" : JSON.stringify(values),
      );
    };

    useEffect(() => {
      try {
        if (typeof value === "string") {
          setSelectedValues(JSON.parse(value));
        }
      } catch (error) {
        setSelectedValues([]);
      }
    }, [value]);

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
      } else if (e.key === "Enter" && freeMode) {
        e.preventDefault();

        const newValues = Array.from(new Set([...selectedValues, filter]));
        setSelectedValues(newValues);

        handleDispatchEvent(newValues);

        setFilter("");
      }
    };

    const handleSelectOption = (optionIndex: number) => {
      const option = filteredOptions[optionIndex];
      const value = typeof option === "string" ? option : option.value;

      const newValues = Array.from(new Set([...selectedValues, value]));
      setSelectedValues(newValues);

      handleDispatchEvent(newValues);

      onClose();
      setFilter("");
    };

    const handleRemove = (index: number) => {
      const newValues = selectedValues.filter((_, i) => i !== index);
      setSelectedValues(newValues);

      handleDispatchEvent(newValues);
    };

    const renderTags = (tags: string[], isPlaceholder: boolean = false) => {
      const tempDisplayedTags = (length?: number) => {
        const slicedTags = length ? [...tags.slice(0, length)] : tags;

        return slicedTags.map((tag, index) => (
          <Tag key={`${tag}-${index}`} size="sm" variant="solid">
            <TagLabel textTransform="none">{tag}</TagLabel>
            {!isPlaceholder && (
              <TagCloseButton onClick={() => handleRemove(index)} />
            )}
          </Tag>
        ));
      };

      const displayedTags =
        collapsed && !isPlaceholder && maxTags && tags.length > maxTags
          ? [
              ...tempDisplayedTags(maxTags),
              <Tag
                key="more-tag"
                size="sm"
                variant="solid"
                onClick={() => setCollapsed(false)}
                cursor="pointer"
              >
                <TagLabel textTransform="none">
                  <Trans
                    i18nKey="more data"
                    values={{ data: tags.length - maxTags }}
                  />
                </TagLabel>
              </Tag>,
            ]
          : tempDisplayedTags();

      return displayedTags;
    };

    return (
      <Popover isOpen={isOpen} initialFocusRef={containerRef}>
        <Box
          ref={containerRef}
          w="full"
          onFocus={isReadOnly ? undefined : onOpen}
        >
          <Input
            tags={[
              ...renderTags(placeholderTags ?? [], true),
              ...renderTags(tags),
            ]}
            suffix={
              <Icon
                as={LuChevronDown}
                transition="transform 0.2s"
                transform={`rotate(${isOpen ? "180deg" : "0deg"})`}
              />
            }
            value={filter}
            isReadOnly={isReadOnly}
            onChange={(e) => setFilter(e.target.value)}
            onKeyDown={handleKeyDown}
            hasPopover
            {...props}
          />
          <Input
            ref={ref}
            id={inputId}
            name={name}
            container={{ display: "none" }}
            value={
              selectedValues.length === 0 ? "" : JSON.stringify(selectedValues)
            }
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
                options={filteredOptions}
                activeIndex={activeIndex}
                filter={filter}
                onChange={handleSelectOption}
                onClose={onClose}
                renderOption={renderOption}
                isLoading={isLoading}
              />
            </Fade>
          </PopoverContent>
        </Portal>
      </Popover>
    );
  },
);

export default MultipleAutocomplete;
