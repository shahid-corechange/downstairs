import { Button, Flex, Heading, Link } from "@chakra-ui/react";
import { Head, Link as InertiaLink, router, usePage } from "@inertiajs/react";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import Input from "@/components/Input";

import AuthLayout from "@/layouts/Auth";

type FormValues = {
  email: string;
};

const ForgetPassword = () => {
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
    router.post("/forgot-password", data, {
      headers: {
        "Accept-Language": localStorage.getItem("language") || "",
      },
      onFinish: () => setIsSubmitting(false),
    });
  });

  return (
    <>
      <Head>
        <title>{t("forget password")}</title>
      </Head>
      <AuthLayout>
        <Heading size={{ base: "xl", md: "lg" }} mb={8}>
          {t("forget password")}
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
          <Button
            type="submit"
            fontSize="sm"
            isLoading={isSubmitting}
            loadingText={t("please wait")}
          >
            {t("submit")}
          </Button>
          <Link as={InertiaLink} href="/login" fontSize="sm" textAlign="center">
            {t("cancel")}
          </Link>
        </Flex>
      </AuthLayout>
    </>
  );
};

export default ForgetPassword;
