import { Flex } from "@chakra-ui/react";
import { useEffect } from "react";
import { useTranslation } from "react-i18next";

import Input from "@/components/Input";
import { useWizard } from "@/components/Wizard/hooks";

import { StepsValues, TimeFormValues } from "../../types";

const TimeStep = () => {
  const { t } = useTranslation();

  const { stepsValues, isValidating, moveTo, onValidateSuccess } = useWizard<
    StepsValues,
    TimeFormValues
  >();

  useEffect(() => {
    if (isValidating) {
      onValidateSuccess({});
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isValidating]);

  return (
    <Flex
      as="form"
      w="full"
      direction="column"
      gap={4}
      onSubmit={() => moveTo("next")}
    >
      <Flex gap={4}>
        <Input
          type="date"
          labelText={t("date start")}
          helperText={t("schedule date start helper text")}
          defaultValue={stepsValues[0].startAt}
          isReadOnly
        />
        <Input
          type="date"
          labelText={t("date end")}
          defaultValue={stepsValues[0].endAt}
          isReadOnly
        />
      </Flex>
      <Flex gap={4}>
        <Input
          labelText={t("time start")}
          helperText={t("subscription time start helper text")}
          defaultValue={stepsValues[0].startTimeAt}
          isReadOnly
        />
        <Input
          labelText={t("time end")}
          helperText={t("subscription time end helper text")}
          defaultValue={stepsValues[0].endTimeAt}
          isReadOnly
        />
      </Flex>
    </Flex>
  );
};

export default TimeStep;
