import { useContext } from "@/hooks/context";

import { WizardContext } from "./contexts";
import { WizardState } from "./types";

export const useWizard = <
  TStepsValues extends Record<string, unknown>[] = Record<string, unknown>[],
  TValues extends Record<string, unknown> = Record<string, unknown>,
>() => {
  const state = useContext(WizardContext);

  return state as unknown as WizardState<TStepsValues, TValues>;
};
