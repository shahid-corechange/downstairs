import {
  FieldErrors,
  UseFormRegister,
  UseFormSetValue,
  UseFormWatch,
} from "react-hook-form";

export type FormValues = {
  key: string;
  value: string;
  description: string;
};

export interface ValueFieldProps {
  errors: FieldErrors<FormValues>;
  register: UseFormRegister<FormValues>;
  watch: UseFormWatch<FormValues>;
  setValue: UseFormSetValue<FormValues>;
}
