import { Flex, TabPanel, Textarea } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import Input from "@/components/Input";

import { PanelProps } from "../../types";

const SwedishPanel = ({ register, errors, ...props }: PanelProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;

  return (
    <TabPanel {...props}>
      <Flex direction="column" gap={4}>
        <Input
          labelText={t("name")}
          errorText={errors.name?.sv_SE?.message || serverErrors["name.sv_SE"]}
          isRequired
          {...register("name.sv_SE", {
            required: t("validation field required"),
          })}
        />
        <Input
          as={Textarea}
          labelText={t("description")}
          errorText={
            errors.description?.sv_SE?.message ||
            serverErrors["description.sv_SE"]
          }
          resize="none"
          isRequired
          {...register("description.sv_SE", {
            required: t("validation field required"),
          })}
        />
      </Flex>
    </TabPanel>
  );
};

export default SwedishPanel;
