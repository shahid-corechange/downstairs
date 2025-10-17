import { TabPanelProps } from "@chakra-ui/react";
import { FieldErrors, UseFormRegister } from "react-hook-form";

export type TaskFormValues = {
  name: {
    sv_SE?: string;
    en_US?: string;
  };
  description: {
    sv_SE?: string;
    en_US?: string;
  };
};

export interface PanelProps extends TabPanelProps {
  register: UseFormRegister<TaskFormValues>;
  errors: FieldErrors<TaskFormValues>;
}
