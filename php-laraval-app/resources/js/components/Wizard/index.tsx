import {
  Box,
  Button,
  Fade,
  Flex,
  FlexProps,
  Icon,
  Spacer,
  Step,
  StepIcon,
  StepIndicator,
  StepNumber,
  StepSeparator,
  StepStatus,
  StepTitle,
  Stepper,
} from "@chakra-ui/react";
import { useSize } from "@chakra-ui/react-use-size";
import {
  Fragment,
  forwardRef,
  isValidElement,
  useEffect,
  useImperativeHandle,
  useMemo,
  useRef,
  useState,
} from "react";
import { useTranslation } from "react-i18next";
import { LuCheck, LuChevronRight, LuChevronsRight } from "react-icons/lu";

import WizardStepComponent, { WizardStepProps } from "./components/WizardStep";
import { WizardContext } from "./contexts";
import { StatefulWizardStep, WizardState, WizardStep } from "./types";

interface WizardProps extends Omit<FlexProps, "children"> {
  children: React.ReactElement<WizardStepProps>[];
  onFinish: (stepsValues: unknown[], toggleFinish: () => void) => void;
}

const Wizard = forwardRef<WizardState, WizardProps>(
  ({ children, onFinish, ...props }, ref) => {
    const { t } = useTranslation();

    const containerRef = useRef<HTMLDivElement>(null);
    const containerSize = useSize(containerRef);

    const [steps, setSteps] = useState<StatefulWizardStep[]>([]);
    const [stepsValues, setStepsValues] = useState<Record<string, unknown>[]>(
      [],
    );
    const [activeStepIndex, setActiveStepIndex] = useState(0);
    const [pendingDestination, setPendingDestination] = useState<
      "next" | number
    >();
    const [isValidating, setIsValidating] = useState(false);
    const [isFinished, setIsFinished] = useState(false);

    const visibleStepIndexes = useMemo(
      () =>
        steps.reduce<number[]>((acc, item, index) => {
          if (!item.isHidden) {
            acc.push(index);
          }
          return acc;
        }, []),
      [steps],
    );

    const isFinalStep = visibleStepIndexes.at(-1) === activeStepIndex;

    const addStep = (step: WizardStep) => {
      setSteps((prevState) => [...prevState, { ...step, isHidden: false }]);
      setStepsValues((prevState) => [...prevState, {}]);
    };

    const hideStep = (index: number) => {
      if (index < 0 || index >= steps.length) {
        return;
      }

      setSteps((prevState) => {
        const newState = [...prevState];
        newState[index].isHidden = true;
        return newState;
      });
    };

    const showStep = (index: number) => {
      if (index < 0 || index >= steps.length) {
        return;
      }

      setSteps((prevState) => {
        const newState = [...prevState];
        newState[index].isHidden = false;
        return newState;
      });
    };

    const nextStep = () => {
      const index = visibleStepIndexes.indexOf(activeStepIndex);

      if (index === visibleStepIndexes.length - 1) {
        setIsFinished(true);
        return;
      }

      setActiveStepIndex(visibleStepIndexes[index + 1]);
    };

    const previousStep = () => {
      const index = visibleStepIndexes.indexOf(activeStepIndex);

      if (index > 0) {
        setActiveStepIndex(visibleStepIndexes[index - 1]);
      }
    };

    const skipToStep = (index: number) => {
      if (visibleStepIndexes.includes(index)) {
        setActiveStepIndex(index < 0 ? 0 : index);
      }
    };

    const setStepValues = (index: number, values: Record<string, unknown>) => {
      setStepsValues((prevState) => {
        const newValues = [...prevState];
        newValues[index] = values;
        return newValues;
      });
    };

    const moveTo = (destination: "next" | "previous" | number) => {
      if (destination === "next") {
        setIsValidating(true);
        setPendingDestination(destination);
      } else if (destination === "previous") {
        previousStep();
      } else if (
        typeof destination === "number" &&
        destination < activeStepIndex
      ) {
        skipToStep(destination);
      } else {
        setIsValidating(true);
        setPendingDestination(destination);
      }
    };

    const toggleFinish = () => {
      setIsFinished((prevState) => !prevState);
    };

    const onValidateSuccess = (values: Record<string, unknown>) => {
      setIsValidating(false);
      setStepsValues((prevState) => {
        prevState[activeStepIndex] = values;
        return prevState;
      });

      if (pendingDestination === "next") {
        nextStep();
      } else if (typeof pendingDestination === "number") {
        skipToStep(pendingDestination);
      }

      setPendingDestination(undefined);
    };

    const onValidateError = () => {
      setIsValidating(false);
      setPendingDestination(undefined);
    };

    useEffect(() => {
      if (steps.length === 0) {
        for (const child of children) {
          if (!isValidElement(child) || child.type !== WizardStepComponent) {
            throw new Error("Wizard children must be WizardStep components");
          }

          addStep({
            title: child.props.title,
            description: child.props.description,
            skipLabel: child.props.skipLabel,
            skipTo: child.props.skipTo,
          });
        }
      }
    }, []);

    useEffect(() => {
      if (isFinished) {
        onFinish(stepsValues, toggleFinish);
      }
    }, [isFinished]);

    const state = {
      steps,
      visibleStepIndexes,
      activeStepIndex,
      stepsValues,
      isValidating,
      isFinalStep,
      isFinished,
      pendingDestination,
      addStep,
      hideStep,
      showStep,
      setStepValues,
      moveTo,
      toggleFinish,
      onValidateSuccess,
      onValidateError,
    };

    useImperativeHandle(ref, () => state, [state]);

    return (
      <WizardContext.Provider value={state}>
        <Flex
          w="full"
          h="full"
          direction="column"
          alignSelf="center"
          flex={1}
          p={6}
          {...props}
        >
          <Stepper
            _horizontal={{
              alignItems: "flex-start",
              justifyContent: "center",
            }}
            mb={12}
            index={activeStepIndex}
          >
            {visibleStepIndexes.map((idx) => (
              <Fragment key={steps[idx].title}>
                <Step style={{ flexGrow: 0 }}>
                  <Flex direction="column" align="center" gap={2}>
                    <StepIndicator>
                      <StepStatus
                        complete={<StepIcon />}
                        incomplete={<StepNumber />}
                        active={<StepNumber />}
                      />
                    </StepIndicator>

                    <Flex direction="column" align="center" textAlign="center">
                      <StepTitle>{steps[idx].title}</StepTitle>
                    </Flex>
                  </Flex>
                </Step>
                <StepSeparator style={{ alignSelf: "center" }} />
              </Fragment>
            ))}
          </Stepper>
          <Box ref={containerRef} overflowX="hidden" mb={8}>
            <Flex
              w={containerSize ? containerSize.width * steps.length : undefined}
              transition="transform 0.3s ease-in-out"
              transform="auto"
              translateX={`${(activeStepIndex / steps.length) * -100}%`}
            >
              {/* We map over the steps not the children to avoid children access empty steps data */}
              {steps.map((_, i) => (
                <Box key={i} w="full" p={2}>
                  <Fade in={activeStepIndex === i} unmountOnExit>
                    {children[i]}
                  </Fade>
                </Box>
              ))}
            </Flex>
          </Box>
          <Spacer />
          <Flex gap={4}>
            {activeStepIndex > 0 && (
              <Button
                colorScheme="gray"
                fontSize="sm"
                leftIcon={<Icon as={LuChevronRight} transform="scaleX(-1)" />}
                onClick={() => moveTo("previous")}
                isDisabled={isValidating || isFinished}
              >
                {t("back")}
              </Button>
            )}
            <Spacer />
            {steps[activeStepIndex] && steps[activeStepIndex].skipTo && (
              <Button
                colorScheme="gray"
                fontSize="sm"
                rightIcon={<Icon as={LuChevronsRight} />}
                onClick={() => moveTo(steps[activeStepIndex].skipTo || 0)}
                isLoading={
                  isValidating && typeof pendingDestination === "number"
                }
                isDisabled={
                  isFinished || (isValidating && pendingDestination === "next")
                }
              >
                {steps[activeStepIndex].skipLabel || t("skip")}
              </Button>
            )}
            <Button
              fontSize="sm"
              rightIcon={
                <Icon
                  as={
                    activeStepIndex < steps.length - 1
                      ? LuChevronRight
                      : LuCheck
                  }
                />
              }
              onClick={() => moveTo("next")}
              loadingText={t("please wait")}
              isLoading={
                isFinished || (isValidating && pendingDestination === "next")
              }
              isDisabled={
                isValidating && typeof pendingDestination === "number"
              }
            >
              {isFinalStep ? t("finish") : t("continue")}
            </Button>
          </Flex>
        </Flex>
      </WizardContext.Provider>
    );
  },
);

export default Wizard;
