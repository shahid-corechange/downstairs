import { Button, Flex, Heading, Image, Text } from "@chakra-ui/react";
import { Head, router, usePage } from "@inertiajs/react";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import Input from "@/components/Input";
import PasswordInput from "@/components/PasswordInput";

import { useFlashToast } from "@/hooks/toast";

import AuthLayout from "@/layouts/Auth";

type FormValues = {
  email: string;
  password: string;
  passwordConfirmation: string;
};

interface CreatePasswordProps {
  payload: string;
  hash: string;
  isExpired: boolean;
}

const CreatePassword = ({ payload, hash, isExpired }: CreatePasswordProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;
  const {
    register,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();
  const [isSubmitting, setIsSubmitting] = useState(false);

  useFlashToast();

  const handleSubmit = formSubmitHandler((data) => {
    setIsSubmitting(true);
    router.post(
      "/create-password",
      { ...data, payload, hash },
      {
        headers: {
          "Accept-Language": localStorage.getItem("language") || "",
        },
        onFinish: () => setIsSubmitting(false),
        onSuccess: () => router.get("/login"),
      },
    );
  });

  const handleResend = () => {
    setIsSubmitting(true);
    router.post(
      "/create-password/resend",
      { payload },
      {
        onFinish: () => setIsSubmitting(false),
      },
    );
  };

  return (
    <>
      <Head>
        <title>{t("create password")}</title>
      </Head>
      {!isExpired ? (
        <AuthLayout>
          <Heading size={{ base: "xl", md: "lg" }} mb={8}>
            {t("create password")}
          </Heading>
          <Flex
            as="form"
            w="full"
            maxW={435}
            direction="column"
            gap={4}
            onSubmit={handleSubmit}
          >
            {Object.keys(serverErrors).length > 0 && (
              <Alert status="error" message={Object.values(serverErrors)[0]} />
            )}

            <Input
              placeholder={t("email")}
              errorText={errors.email?.message || serverErrors.email}
              {...register("email", {
                required: t("validation field required"),
              })}
            />
            <PasswordInput
              placeholder={t("password")}
              errorText={errors.password?.message || serverErrors.password}
              {...register("password", {
                required: t("validation field required"),
              })}
            />
            <PasswordInput
              placeholder={t("password confirmation")}
              errorText={
                errors.passwordConfirmation?.message ||
                serverErrors.passwordConfirmation
              }
              {...register("passwordConfirmation", {
                required: t("validation field required"),
              })}
            />
            <Button
              type="submit"
              fontSize="sm"
              isLoading={isSubmitting}
              loadingText={t("please wait")}
            >
              {t("submit")}
            </Button>
          </Flex>
        </AuthLayout>
      ) : (
        <Flex
          minH="100vh"
          maxW="3xl"
          w="full"
          mx="auto"
          direction="column"
          align="center"
          justify="center"
          gap={4}
        >
          <Image
            src="/images/send-email.svg"
            alt="Verify Email"
            h="450px"
            w="600px"
          />
          <Heading color="brand.500" textAlign="center">
            {t("page expired")}
          </Heading>
          <Text color="gray.600" textAlign="center" mb={4}>
            {t("create password page expired")}
          </Text>
          <Button
            fontSize="sm"
            loadingText={t("please wait")}
            isLoading={isSubmitting}
            onClick={handleResend}
          >
            {t("request create password")}
          </Button>
        </Flex>
      )}
    </>
  );
};

export default CreatePassword;
