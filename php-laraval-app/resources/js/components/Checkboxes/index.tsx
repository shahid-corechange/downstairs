import {
  Checkbox,
  CheckboxGroup,
  CheckboxGroupProps,
  CheckboxProps,
  FormControl,
  FormControlProps,
  FormErrorMessage,
  FormErrorMessageProps,
  FormHelperText,
  FormHelperTextProps,
  FormLabel,
  FormLabelProps,
  Grid,
  GridItem,
  GridProps,
} from "@chakra-ui/react";
import { forwardRef } from "react";

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

export interface CheckboxOption {
  label: string;
  value: string | number;
}

interface CheckboxesProps
  extends Omit<CheckboxProps, "value" | "isReadOnly" | "readOnly"> {
  options: (string | CheckboxOption)[];
  size?: keyof typeof fontSizes;
  container?: FormControlProps;
  label?: FormLabelProps;
  labelText?: string;
  group?: CheckboxGroupProps;
  grid?: GridProps;
  error?: FormErrorMessageProps;
  errorText?: string;
  helper?: FormHelperTextProps;
  helperText?: string;
  value?: (string | number)[];
  isReadOnly?: boolean | ((option: CheckboxOption) => boolean);
}

const Checkboxes = forwardRef<HTMLInputElement, CheckboxesProps>(
  (
    {
      options,
      container,
      label,
      labelText,
      group,
      grid,
      error,
      errorText,
      helper,
      helperText,
      value,
      isRequired,
      isReadOnly,
      size = "sm",
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
          <FormLabel htmlFor="" fontSize={fontSizes[size]} {...label}>
            {labelText}
          </FormLabel>
        )}
        <CheckboxGroup
          size={size === "xs" ? "sm" : size}
          value={value}
          {...group}
        >
          <Grid templateColumns="repeat(3, 1fr)" {...grid}>
            {options.map((option, i) => (
              <GridItem key={i}>
                <Checkbox
                  ref={ref}
                  value={typeof option === "string" ? option : option.value}
                  _invalid={{
                    "& .chakra-checkbox__control": { borderColor: "inherit" },
                  }}
                  _readOnly={{
                    opacity: 0.4,
                    cursor: "not-allowed",
                  }}
                  readOnly={
                    typeof isReadOnly === "function"
                      ? isReadOnly(
                          typeof option === "string"
                            ? { label: option, value: option }
                            : option,
                        )
                      : isReadOnly
                  }
                  {...props}
                >
                  {typeof option === "string" ? option : option.label}
                </Checkbox>
              </GridItem>
            ))}
          </Grid>
        </CheckboxGroup>
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

export default Checkboxes;
