import {
  Box,
  BoxProps,
  Input as ChakraInput,
  InputProps as ChakraInputProps,
  Flex,
  FlexProps,
  FormControl,
  FormControlProps,
  FormErrorMessage,
  FormErrorMessageProps,
  FormHelperText,
  FormHelperTextProps,
  FormLabel,
  FormLabelProps,
  InputGroup,
  InputLeftAddon,
  InputLeftElement,
  InputLeftElementProps,
  InputRightAddon,
  InputRightElement,
  InputRightElementProps,
  PopoverTrigger,
  Spacer,
} from "@chakra-ui/react";
import React, { forwardRef, useEffect, useState } from "react";

import { getColor } from "@/utils/color";

const sizes = {
  xs: "2rem",
  sm: "2.5rem",
  md: "3rem",
  lg: "3.5rem",
};

const fontSizes = {
  xs: "xs",
  sm: "small",
  md: "sm",
  lg: "md",
};

const helperFontSizes = {
  xs: "2xs",
  sm: "xs",
  md: "small",
  lg: "sm",
};

const variants = {
  outline: {
    border: "1px",
    borderColor: "inherit",
  },
  filled: {
    border: "1px",
    borderColor: "inherit",
    bg: "gray.100",
    _dark: {
      bg: "whiteAlpha.50",
    },
  },
  flushed: {
    border: "none",
    borderBottom: "1px",
    borderColor: "inherit",
    rounded: "none",
  },
  unstyled: {
    border: "none",
    rounded: "none",
    _focusWithin: {},
    _invalid: {},
  },
};

export interface InputProps extends Omit<ChakraInputProps, "prefix"> {
  groupRef?: React.RefObject<HTMLDivElement>;
  variant?: "outline" | "filled" | "flushed" | "unstyled";
  size?: keyof typeof sizes;
  container?: FormControlProps;
  labelContainer?: FlexProps;
  label?: FormLabelProps;
  labelText?: string;
  labelEnd?: React.ReactNode;
  prefixContainer?: InputLeftElementProps;
  prefix?: React.ReactNode;
  suffixContainer?: InputRightElementProps;
  suffix?: React.ReactNode;
  inputContainer?: BoxProps;
  tags?: React.ReactNode[];
  error?: FormErrorMessageProps;
  errorText?: string;
  helper?: FormHelperTextProps;
  helperText?: React.ReactNode;
  debounce?: number;
  leftAddon?: React.ReactNode;
  leftAddonContainer?: InputLeftElementProps;
  rightAddon?: React.ReactNode;
  rightAddonContainer?: InputRightElementProps;
  paddingOnInput?: boolean;
  hasPopover?: boolean;
  onChangeDebounce?: (value: string | number | readonly string[]) => void;
}

const Input = forwardRef<HTMLInputElement, InputProps>(
  (
    {
      groupRef,
      container,
      labelContainer,
      label,
      labelText,
      labelEnd,
      prefixContainer,
      prefix,
      suffixContainer,
      suffix,
      inputContainer,
      tags,
      error,
      errorText,
      helper,
      helperText,
      onChangeDebounce,
      defaultValue,
      value,
      onChange,
      isRequired,
      variant = "outline",
      size = "sm",
      debounce = 500,
      paddingOnInput = false,
      hasPopover = false,
      leftAddon,
      leftAddonContainer,
      rightAddon,
      rightAddonContainer,
      ...props
    },
    ref,
  ) => {
    const [debouncedValue, setDebouncedValue] = useState(value || "");

    useEffect(() => {
      const timeout = setTimeout(() => {
        if (debouncedValue !== value) {
          onChangeDebounce?.(debouncedValue);
        }
      }, debounce);

      return () => {
        clearTimeout(timeout);
      };

      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [debouncedValue, debounce]);

    useEffect(() => {
      setDebouncedValue(value || "");
    }, [value]);

    const renderRoundedBox = (
      left?: React.ReactNode,
      right?: React.ReactNode,
    ) => {
      if (left && right) {
        return "none";
      } else if (left) {
        return "0 6px 6px 0";
      } else if (right) {
        return "6px 0 0 6px";
      } else return "md";
    };

    return (
      <FormControl
        {...container}
        isInvalid={!!errorText}
        isRequired={isRequired}
      >
        {labelText && (
          <Flex
            direction="row"
            {...(labelEnd && { mb: 2 })}
            {...labelContainer}
          >
            <FormLabel fontSize={fontSizes[size]} {...label}>
              {labelText}
            </FormLabel>
            {labelEnd && (
              <>
                <Spacer />
                {labelEnd}
              </>
            )}
          </Flex>
        )}
        <InputGroup ref={groupRef} size={size}>
          {prefix && (
            <InputLeftElement h="full" w={sizes[size]} {...prefixContainer}>
              {prefix}
            </InputLeftElement>
          )}
          {leftAddon && (
            <InputLeftAddon
              minH={sizes[size]}
              py={size === "xs" ? 1 : 2}
              pl={prefix ? sizes[size] : variant === "flushed" ? 0 : 4}
              pr={suffix ? sizes[size] : variant === "flushed" ? 0 : 4}
              rounded="md"
              {...leftAddonContainer}
            >
              {leftAddon}
            </InputLeftAddon>
          )}
          <PopoverWrapper hasPopover={hasPopover}>
            <Box
              display="flex"
              flex={1}
              gap={2}
              flexWrap="wrap"
              minH={sizes[size]}
              py={!paddingOnInput ? (size === "xs" ? 1 : 2) : undefined}
              pl={
                !paddingOnInput
                  ? prefix
                    ? sizes[size]
                    : variant === "flushed"
                    ? 0
                    : 4
                  : undefined
              }
              pr={
                !paddingOnInput
                  ? suffix
                    ? sizes[size]
                    : variant === "flushed"
                    ? 0
                    : 4
                  : undefined
              }
              rounded={renderRoundedBox(leftAddon, rightAddon)}
              transition="all 0.2s"
              data-invalid={errorText ? "true" : undefined}
              _focusWithin={{
                bg:
                  props.isReadOnly || variant !== "filled"
                    ? undefined
                    : "transparent",
                borderColor: errorText ? "red.500" : "brand.500",
                boxShadow: `${
                  variant === "flushed" ? "0 1px 0 0" : "0 0 0 1px"
                } ${getColor(errorText ? "red.500" : "brand.500")}`,
              }}
              _invalid={{
                borderColor: "red.500",
                boxShadow: `${
                  variant === "flushed" ? "0 1px 0 0" : "0 0 0 1px"
                } ${getColor("red.500")}`,
              }}
              {...variants[variant]}
              sx={
                props.isReadOnly
                  ? {
                      bg: "gray.100 !important",
                      _dark: {
                        bg: "gray.600 !important",
                      },
                    }
                  : {}
              }
              {...inputContainer}
            >
              {tags}
              <ChakraInput
                ref={ref}
                variant="unstyled"
                w="full"
                flex={1}
                minW="100px"
                py={paddingOnInput ? (size === "xs" ? 1 : 2) : undefined}
                pl={
                  paddingOnInput
                    ? prefix
                      ? sizes[size]
                      : variant === "flushed"
                      ? 0
                      : 4
                    : undefined
                }
                pr={
                  paddingOnInput
                    ? suffix
                      ? sizes[size]
                      : variant === "flushed"
                      ? 0
                      : 4
                    : undefined
                }
                fontSize={fontSizes[size]}
                textOverflow="ellipsis"
                rounded="none"
                border="none"
                _focusVisible={{
                  boxShadow: "none",
                }}
                _invalid={{
                  boxShadow: "none",
                }}
                {...(defaultValue
                  ? { defaultValue }
                  : {
                      value: onChangeDebounce ? debouncedValue : value,
                      onChange: (e) => {
                        onChange?.(e);
                        setDebouncedValue(e.target.value);
                      },
                    })}
                {...props}
              />
            </Box>
          </PopoverWrapper>
          {rightAddon && (
            <InputRightAddon
              minH={sizes[size]}
              py={size === "xs" ? 1 : 2}
              pl={prefix ? sizes[size] : variant === "flushed" ? 0 : 4}
              pr={suffix ? sizes[size] : variant === "flushed" ? 0 : 4}
              rounded="md"
              {...rightAddonContainer}
            >
              {rightAddon}
            </InputRightAddon>
          )}

          {suffix && (
            <InputRightElement h="full" w={sizes[size]} {...suffixContainer}>
              {suffix}
            </InputRightElement>
          )}
        </InputGroup>
        {errorText && (
          <FormErrorMessage fontSize={helperFontSizes[size]} {...error}>
            {errorText}
          </FormErrorMessage>
        )}
        {helperText && !errorText && (
          <FormHelperText fontSize={helperFontSizes[size]} {...helper}>
            {helperText}
          </FormHelperText>
        )}
      </FormControl>
    );
  },
);

interface PopoverWrapperProps {
  hasPopover: boolean;
  children: React.ReactNode;
}

const PopoverWrapper = ({ hasPopover, children }: PopoverWrapperProps) => {
  return hasPopover ? <PopoverTrigger>{children}</PopoverTrigger> : children;
};

export default Input;
