import { createContext } from "react";

import { ComposerRowState, InputComposerState } from "../types";

export const InputComposerContext = createContext<InputComposerState | null>(
  null,
);

export const ComposerRowContext = createContext<ComposerRowState | null>(null);
