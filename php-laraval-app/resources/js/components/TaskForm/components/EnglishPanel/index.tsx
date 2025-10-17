import { Flex, TabPanel, Textarea } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import Input from "@/components/Input";

import { PanelProps } from "../../types";

const EnglishPanel = ({ register, errors, ...props }: PanelProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;

  return (
    <TabPanel {...props}>
      <Flex direction="column" gap={4}>
        <Input
          labelText={t("name")}
          errorText={errors.name?.en_US?.message || serverErrors["name.en_US"]}
          isRequired
          {...register("name.en_US", {
            required: t("validation field required"),
          })}
        />
        <Input
          as={Textarea}
          labelText={t("description")}
          errorText={
            errors.description?.en_US?.message ||
            serverErrors["description.en_US"]
          }
          resize="none"
          isRequired
          {...register("description.en_US", {
            required: t("validation field required"),
          })}
        />
      </Flex>
    </TabPanel>
  );
};

export default EnglishPanel;
