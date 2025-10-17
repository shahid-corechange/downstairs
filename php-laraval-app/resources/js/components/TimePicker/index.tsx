import { Icon } from "@chakra-ui/react";
import { forwardRef } from "react";
import { LuClock } from "react-icons/lu";

import { QUARTERS_IN_DAYS } from "@/constants/datetime";

import Autocomplete from "../Autocomplete";
import { InputProps } from "../Input";

const TimePicker = forwardRef<HTMLInputElement, InputProps>((props, ref) => {
  return (
    <Autocomplete
      ref={ref}
      options={QUARTERS_IN_DAYS}
      placeholder="_ _ : _ _"
      prefix={<Icon as={LuClock} />}
      {...props}
    />
  );
});

export default TimePicker;
