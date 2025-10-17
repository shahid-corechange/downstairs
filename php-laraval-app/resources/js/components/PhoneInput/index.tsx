import { Box, useId } from "@chakra-ui/react";
import { forwardRef, useEffect, useRef, useState } from "react";

import { dispatchInputChangeEvent } from "@/utils/event";

import Autocomplete from "../Autocomplete";
import Input, { InputProps } from "../Input";

interface PhoneInputProps extends InputProps {
  value?: string;
  defaultCode?: string;
  dialCodes?: string[];
}

const PhoneInput = forwardRef<HTMLInputElement, PhoneInputProps>(
  (
    {
      onChange,
      dialCodes = [],
      name,
      onBlur,
      value = "",
      defaultCode = "+46",
      ...props
    },
    ref,
  ) => {
    const containerRef = useRef<HTMLDivElement>(null);
    const [dialCode, setDialCode] = useState(defaultCode);
    const [phoneNumber, setPhoneNumber] = useState("");
    const inputId = useId(undefined, "phone-input");

    useEffect(() => {
      const [code, number] = value.split(" ");
      setDialCode(code || defaultCode);
      setPhoneNumber(number || "");
    }, [value]);

    const handleChangePhoneNumber = (
      e: React.ChangeEvent<HTMLInputElement>,
    ) => {
      setPhoneNumber(e.target.value);

      const combinedPhoneNumber = `${dialCode} ${e.target.value}`;

      const input = document.getElementById(inputId) as HTMLInputElement;

      if (!input) {
        return;
      }

      dispatchInputChangeEvent(input, combinedPhoneNumber);
    };

    const handleChangeCountryCode = (
      e: React.ChangeEvent<HTMLInputElement>,
    ) => {
      setDialCode(e.target.value);
    };

    return (
      <Box ref={containerRef}>
        <Input
          type="tel"
          value={phoneNumber}
          onChange={handleChangePhoneNumber}
          leftAddonContainer={{ p: 0, maxW: "100px" }}
          leftAddon={
            <Autocomplete
              options={dialCodes}
              variant="unstyled"
              onChange={handleChangeCountryCode}
              value={dialCode}
              minW={0}
            />
          }
          {...props}
        />
        <Input
          ref={ref}
          id={inputId}
          name={name}
          container={{ display: "none" }}
          onChange={onChange}
          onBlur={onBlur}
        />
      </Box>
    );
  },
);

export default PhoneInput;
