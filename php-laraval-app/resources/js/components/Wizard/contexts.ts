import { createContext } from "react";

import { WizardState } from "./types";

export const WizardContext = createContext<WizardState | null>(null);
