import { InputValue } from "@/types";

export interface InputComposerState {
  rows: (Record<string, InputValue> & { _id: number })[];
  onAdd: () => void;
  onRemove: (index: number) => void;
  onChange: (index: number, key: string, value: InputValue) => void;
  reset: () => void;
  maxRows?: number;
}

export interface ComposerRowState {
  index: number;
  getValue: (key: string) => InputValue;
  onChange: (key: string, value: InputValue) => void;
}
