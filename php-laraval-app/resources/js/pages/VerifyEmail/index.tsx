import { Button, Flex, Heading, Image, Text } from "@chakra-ui/react";
import { Head, router } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import { useFlashToast } from "@/hooks/toast";

const VerifyEmail = () => {
  const { t } = useTranslation();

  useFlashToast();

  const [isLoading, setIsLoading] = useState(false);

  const resendHandler = () => {
    setIsLoading(true);
    router.post("/email/verification-notification", undefined, {
      onFinish: () => setIsLoading(false),
    });
  };

  return (
    <>
      <Head>
        <title>{t("verify email")}</title>
      </Head>
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
          {t("verify email")}
        </Heading>
        <Text color="gray.600" textAlign="center" mb={4}>
          {t("verify email description")}
        </Text>
        <Button
          fontSize="sm"
          loadingText={t("please wait")}
          isLoading={isLoading}
          onClick={resendHandler}
        >
          {t("verify email resend")}
        </Button>
      </Flex>
    </>
  );
};

export default VerifyEmail;
