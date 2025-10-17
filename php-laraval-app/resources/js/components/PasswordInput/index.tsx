import { IconButton } from "@chakra-ui/react";
import { forwardRef, useState } from "react";
import { AiOutlineEye, AiOutlineEyeInvisible } from "react-icons/ai";

import Input, { InputProps } from "@/components/Input";

export interface PasswordInputProps extends Omit<InputProps, "type"> {}

const PasswordInput = forwardRef<HTMLInputElement, PasswordInputProps>(
  (props, ref) => {
    const [show, setShow] = useState(false);

    const togglePassword = () => {
      setShow((prev) => !prev);
    };

    return (
      <Input
        {...props}
        type={show ? "text" : "password"}
        ref={ref}
        suffix={
          <IconButton
            variant="ghost"
            size="sm"
            aria-label="Toggle Password"
            onClick={togglePassword}
            isRound
          >
            {show ? <AiOutlineEyeInvisible /> : <AiOutlineEye />}
          </IconButton>
        }
      />
    );
  },
);

export default PasswordInput;
