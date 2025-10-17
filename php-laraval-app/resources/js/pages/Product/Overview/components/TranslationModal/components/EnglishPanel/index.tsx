import {
  Button,
  Flex,
  TabPanel,
  TabPanelProps,
  Textarea,
} from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Input from "@/components/Input";

import Product from "@/types/product";

type FormValues = {
  name?: string;
  description?: string;
};

interface PanelProps extends TabPanelProps {
  product: Product;
}

const EnglishPanel = ({ product, ...props }: PanelProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;
  const {
    register,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>({
    defaultValues: {
      name: product.translations?.en_US.name?.value,
      description: product.translations?.en_US.description?.value,
    },
  });

  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = formSubmitHandler((values) => {
    setIsSubmitting(true);

    router.patch(
      `/products/${product.id}/translations`,
      {
        language: "en_US",
        translations: {
          name: {
            id: product.translations?.en_US.name.id,
            value: values.name,
          },
          description: {
            id: product.translations?.en_US.description.id,
            value: values.description,
          },
        },
      },
      {
        onFinish: () => setIsSubmitting(false),
      },
    );
  });

  return (
    <TabPanel {...props}>
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={handleSubmit}
        autoComplete="off"
        noValidate
      >
        <Input
          labelText={t("name")}
          errorText={errors.name?.message || serverErrors.name}
          {...register("name")}
        />
        <Input
          as={Textarea}
          labelText={t("description")}
          errorText={errors.description?.message || serverErrors.description}
          resize="none"
          {...register("description")}
        />
        <Flex justify="right" mt={4} gap={4}>
          <Button
            type="submit"
            fontSize="sm"
            isLoading={isSubmitting}
            loadingText={t("please wait")}
          >
            {t("save")}
          </Button>
        </Flex>
      </Flex>
    </TabPanel>
  );
};

export default EnglishPanel;
