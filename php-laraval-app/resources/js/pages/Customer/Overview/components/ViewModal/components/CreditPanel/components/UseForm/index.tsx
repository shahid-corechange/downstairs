import { Button, Flex, Icon, Textarea } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";
import { AiOutlineMinus } from "react-icons/ai";

import Input from "@/components/Input";

import { ViewModalPageProps } from "@/pages/Customer/Overview/types";

import { PageProps } from "@/types";

type FormValues = {
  amount: number;
  description: string;
};

interface UseFormProps {
  userId: number;
  onCancel: () => void;
  onRefetch: () => void;
}

const UseForm = ({ userId, onCancel, onRefetch }: UseFormProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } =
    usePage<PageProps<ViewModalPageProps>>().props;

  const {
    register,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = formSubmitHandler(({ amount, ...values }) => {
    setIsSubmitting(true);

    router.post(
      "/credits",
      {
        ...values,
        amount: -amount,
        userId,
      },
      {
        onFinish: () => setIsSubmitting(false),
        onSuccess: () => {
          onCancel();
          onRefetch();
        },
      },
    );
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
      <Input
        type="number"
        labelText={t("amount")}
        errorText={errors.amount?.message || serverErrors.amount}
        prefix={<Icon as={AiOutlineMinus} />}
        min={1}
        {...register("amount", {
          required: t("validation field required"),
          valueAsNumber: true,
          min: {
            value: 1,
            message: t("validation field min", { min: 1 }),
          },
        })}
        isRequired
      />
      <Input
        as={Textarea}
        labelText={t("description")}
        errorText={errors.description?.message || serverErrors.description}
        resize="none"
        {...register("description", {
          required: t("validation field required"),
        })}
        isRequired
      />
      <Flex justify="right" mt={4} gap={4}>
        <Button colorScheme="gray" fontSize="sm" onClick={onCancel}>
          {t("close")}
        </Button>
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
  );
};

export default UseForm;
