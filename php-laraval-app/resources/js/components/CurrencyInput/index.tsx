import { Box } from "@chakra-ui/react";
import { forwardRef, useId } from "react";

import { formatCurrency, parseCurrency } from "@/utils/currency";
import { dispatchInputChangeEvent } from "@/utils/event";

import Input, { InputProps } from "../Input";

interface CurrencyInputProps extends Omit<InputProps, "type" | "value"> {
  language: string;
  currency: string;
  value: number;
  decimalPlaces?: number;
}

const CurrencyInput = forwardRef<HTMLInputElement, CurrencyInputProps>(
  (
    {
      language,
      currency,
      value,
      name,
      onChange,
      onBlur,
      decimalPlaces = 2,
      ...props
    },
    ref,
  ) => {
    const inputId = useId();
    const formattedValue = formatCurrency(
      language,
      currency,
      value,
      decimalPlaces,
    );

    const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
      const sourceInput = event.target;
      const oldValue = sourceInput.value;
      const oldPosition = sourceInput.selectionStart;

      const hiddenInput = document.getElementById(inputId);

      if (hiddenInput) {
        const parsedValue = parseCurrency(
          language,
          currency,
          event.target.value,
          decimalPlaces,
        );
        dispatchInputChangeEvent(hiddenInput, parsedValue);
      }

      // Wait for the sourceInput to update with the new formatted value
      requestAnimationFrame(() => {
        if (!oldPosition) {
          return;
        }

        // Calculate the new position based on the length difference
        const newValue = sourceInput.value;
        const lengthDiffBeforeDecimal = newValue.length - oldValue.length;
        const newPosition = oldPosition + lengthDiffBeforeDecimal;

        sourceInput.setSelectionRange(newPosition, newPosition);
      });
    };

    return (
      <Box>
        <Input
          {...props}
          type="text"
          value={formattedValue}
          onChange={handleChange}
        />
        <Input
          ref={ref}
          id={inputId}
          name={name}
          type="number"
          container={{ display: "none" }}
          value={value}
          onChange={onChange}
          onBlur={onBlur}
        />
      </Box>
    );
  },
);

export default CurrencyInput;
