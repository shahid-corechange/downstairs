export interface WizardStep {
  title: string;
  description: string;
  skipLabel?: string;
  skipTo?: number;
}

export interface StatefulWizardStep extends WizardStep {
  isHidden: boolean;
}

export interface WizardState<
  TStepsValues extends Record<string, unknown>[] = Record<string, unknown>[],
  TValues extends Record<string, unknown> = Record<string, unknown>,
> {
  steps: StatefulWizardStep[];
  visibleStepIndexes: number[];
  activeStepIndex: number;
  stepsValues: TStepsValues;
  isValidating: boolean;
  isFinalStep: boolean;
  isFinished: boolean;
  addStep: (step: WizardStep) => void;
  hideStep: (index: number) => void;
  showStep: (index: number) => void;
  setStepValues: <T extends number>(index: T, values: TStepsValues[T]) => void;
  moveTo: (destination: "next" | "previous" | number) => void;
  toggleFinish: () => void;
  onValidateSuccess: (values: TValues) => void;
  onValidateError: () => void;
  pendingDestination?: "next" | number;
}
