import {
  Select as ChakraSelect,
  SelectProps as ChakraSelectProps,
  FormControl,
  FormControlProps,
  FormErrorMessage,
  FormErrorMessageProps,
  FormHelperText,
  FormHelperTextProps,
  FormLabel,
  FormLabelProps,
  InputGroup,
  InputLeftElement,
  InputLeftElementProps,
} from "@chakra-ui/react";
import { forwardRef } from "react";

export interface SelectProps extends Omit<ChakraSelectProps, "prefix"> {
  container?: FormControlProps;
  label?: FormLabelProps;
  labelText?: string;
  prefixContainer?: InputLeftElementProps;
  prefix?: React.ReactNode;
  error?: FormErrorMessageProps;
  errorText?: string;
  helper?: FormHelperTextProps;
  helperText?: string;
}

const Select = forwardRef<HTMLSelectElement, SelectProps>(
  (
    {
      container,
      label,
      labelText,
      // eslint-disable-next-line @typescript-eslint/no-unused-vars
      prefixContainer,
      prefix,
      error,
      errorText,
      helper,
      helperText,
      size,
      isRequired,
      ...props
    },
    ref,
  ) => {
    return (
      <FormControl
        {...container}
        isInvalid={!!errorText}
        isRequired={isRequired}
      >
        {labelText && (
          <FormLabel fontSize={size ?? "sm"} {...label}>
            {labelText}
          </FormLabel>
        )}
        <InputGroup size={size}>
          {prefix && <InputLeftElement>{prefix}</InputLeftElement>}
          <ChakraSelect
            ref={ref}
            focusBorderColor={errorText ? "red.500" : "brand.500"}
            fontSize="sm"
            textOverflow="ellipsis"
            {...props}
          />
        </InputGroup>
        {errorText && (
          <FormErrorMessage {...error}>{errorText}</FormErrorMessage>
        )}
        {helperText && !errorText && (
          <FormHelperText {...helper}>{helperText}</FormHelperText>
        )}
      </FormControl>
    );
  },
);

export default Select;
