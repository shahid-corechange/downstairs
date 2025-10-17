import { Button, Flex, Tab, TabList, TabPanels, Tabs } from "@chakra-ui/react";
import { useState } from "react";
import { SubmitErrorHandler, SubmitHandler, useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import EnglishPanel from "./components/EnglishPanel";
import SwedishPanel from "./components/SwedishPanel";
import { TaskFormValues } from "./types";

interface TaskFormProps {
  isLoading: boolean;
  onSubmit: SubmitHandler<TaskFormValues>;
  onCancel: () => void;
  defaultValues?: Partial<TaskFormValues>;
  onInvalid?: SubmitErrorHandler<TaskFormValues>;
}

const TaskForm = ({
  isLoading,
  onSubmit,
  onCancel,
  defaultValues,
  onInvalid,
}: TaskFormProps) => {
  const { t } = useTranslation();
  const [activeTabIndex, setActiveTabIndex] = useState(0);

  const {
    register,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<TaskFormValues>({ defaultValues });

  const handleSubmit = formSubmitHandler(onSubmit, (errors) => {
    setActiveTabIndex(errors.name?.sv_SE || errors.description?.sv_SE ? 0 : 1);
    onInvalid?.(errors);
  });

  return (
    <Flex
      as="form"
      direction="column"
      gap={4}
      onSubmit={handleSubmit}
      autoComplete="off"
      noValidate
    >
      <Tabs
        index={activeTabIndex}
        onChange={(index) => setActiveTabIndex(index)}
      >
        <TabList>
          <Tab>{t("language sv_se")}</Tab>
          <Tab>{t("language en_us")}</Tab>
        </TabList>
        <TabPanels>
          <SwedishPanel register={register} errors={errors} />
          <EnglishPanel register={register} errors={errors} />
        </TabPanels>
      </Tabs>
      <Flex justify="right" mt={4} gap={4}>
        <Button colorScheme="gray" fontSize="sm" onClick={onCancel}>
          {t("close")}
        </Button>
        <Button
          type="submit"
          fontSize="sm"
          isLoading={isLoading}
          loadingText={t("please wait")}
        >
          {t("save")}
        </Button>
      </Flex>
    </Flex>
  );
};

export default TaskForm;
