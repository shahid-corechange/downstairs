import { WizardState } from "@/components/Wizard/types";

export const handleWizardErrorNavigation = (
  errors: Record<string, string>,
  stepsValues: Record<string, unknown>[],
  wizardRef: React.RefObject<WizardState>,
): void => {
  const field = Object.keys(errors)[0];

  for (let i = 0; i < stepsValues.length; i++) {
    if (Object.keys(stepsValues[i]).includes(field)) {
      wizardRef.current?.moveTo(i);
      return;
    }
  }

  wizardRef.current?.moveTo(0);
};
