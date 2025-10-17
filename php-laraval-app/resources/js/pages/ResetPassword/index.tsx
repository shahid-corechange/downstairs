import { Button, Flex, Heading, Link } from "@chakra-ui/react";
import { Head, Link as InertiaLink, router, usePage } from "@inertiajs/react";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import Input from "@/components/Input";
import PasswordInput from "@/components/PasswordInput";

import AuthLayout from "@/layouts/Auth";

type FormValues = {
  email: string;
  password: string;
  passwordConfirmation: string;
};

interface ResetPasswordProps {
  token: string;
}

const ResetPassword = ({ token }: ResetPasswordProps) => {
  const { t } = useTranslation();
  const { errors: serverErrors } = usePage().props;
  const {
    register,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = formSubmitHandler((data) => {
    setIsSubmitting(true);
    router.post(
      "/reset-password",
      { ...data, token },
      {
        headers: {
          "Accept-Language": localStorage.getItem("language") || "",
        },
        onFinish: () => setIsSubmitting(false),
        onSuccess: () => router.get("/login"),
      },
    );
  });

  return (
    <>
      <Head>
        <title>{t("reset password")}</title>
      </Head>
      <AuthLayout>
        <Heading size={{ base: "xl", md: "lg" }} mb={8}>
          {t("reset password")}
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
            {...register("email", { required: t("validation field required") })}
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
          <Link as={InertiaLink} href="/login" fontSize="sm" textAlign="center">
            {t("back")}
          </Link>
        </Flex>
      </AuthLayout>
    </>
  );
};

export default ResetPassword;
