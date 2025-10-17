import { Flex, FlexProps } from "@chakra-ui/react";
import { useEffect, useRef, useState } from "react";

import Input from "../Input";

interface InputOTPProps extends Omit<FlexProps, "onChange"> {
  length: number;
  onChange: (otp: string) => void;
  isDisabled?: boolean;
}

const InputOTP = ({
  length,
  onChange,
  isDisabled,
  ...props
}: InputOTPProps) => {
  const [otp, setOtp] = useState<string[]>(Array.from({ length }, () => ""));
  const inputRefs = useRef<HTMLInputElement[]>([]);

  useEffect(() => {
    if (otp.filter(Boolean).length === length) {
      onChange(otp.join(""));
    }
  }, [otp]);

  return (
    <Flex gap={4} {...props}>
      {Array.from({ length }).map((_, index) => (
        <Input
          key={index}
          ref={(ref) => ref && inputRefs.current.push(ref)}
          h={10}
          fontSize="3xl"
          textAlign="center"
          fontWeight="bold"
          minWidth="unset"
          maxLength={1}
          value={otp[index]}
          autoFocus={index === 0}
          onKeyDown={(e) => (e.key < "0" || e.key > "9") && e.preventDefault()}
          onKeyUp={(e) => {
            if (e.key === "Backspace") {
              setOtp((prev) => {
                const next = [...prev];
                next[index] = "";
                return next;
              });

              if (index > 0) {
                inputRefs.current[index - 1].focus();
              }
            } else if (e.key >= "0" && e.key <= "9") {
              setOtp((prev) => {
                const next = [...prev];
                next[index] = e.key;
                return next;
              });

              if (index < length - 1) {
                inputRefs.current[index + 1].focus();
              }
            } else if (e.key === "ArrowLeft" && index > 0) {
              inputRefs.current[index - 1].focus();
            } else if (e.key === "ArrowRight" && index < length - 1) {
              inputRefs.current[index + 1].focus();
            }
          }}
          isDisabled={isDisabled}
        />
      ))}
    </Flex>
  );
};

export default InputOTP;
