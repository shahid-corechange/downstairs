import { Center, Flex, Heading, Image, Text } from "@chakra-ui/react";
import { useTranslation } from "react-i18next";

import { useFlashToast } from "@/hooks/toast";

interface AuthLayoutProps {
  children: React.ReactNode;
}

const AuthLayout = ({ children }: AuthLayoutProps) => {
  const { t } = useTranslation();
  useFlashToast();

  return (
    <Flex direction={{ base: "column", lg: "row" }} minH="100vh">
      <Center flexDir="column" flex={1} p={12}>
        <Image
          height="50px"
          mb={4}
          display={{ base: "block", lg: "none" }}
          src="/images/small-logo-b.png"
          alt="Logo"
        />

        {children}
      </Center>

      <Center
        flexDir="column"
        flex={1}
        display={{ base: "none", lg: "flex" }}
        bg="brand.500"
        p={12}
      >
        <Image height={75} src="/images/logo.png" alt="Logo" />

        <Image
          width={275}
          src="/images/you-need-help-white.png"
          alt="Do you need help? we are Downstairs"
          my={8}
        />

        <Heading size="lg" color="white" textAlign="center" mb={4}>
          {t("brand slogan")}
        </Heading>

        <Text fontSize="sm" color="white" align="center">
          {t("brand description")}
        </Text>
      </Center>
    </Flex>
  );
};

export default AuthLayout;
