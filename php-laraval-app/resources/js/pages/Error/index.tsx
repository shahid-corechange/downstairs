import {
  Button,
  Container,
  Divider,
  Flex,
  Heading,
  Link,
} from "@chakra-ui/react";
import { Head } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

interface ErrorProps {
  code: string;
  message: string;
}

const Error = ({ code, message }: ErrorProps) => {
  const { t } = useTranslation();

  return (
    <>
      <Head>
        <title>{code}</title>
      </Head>
      <Container
        height="100vh"
        maxW="3xl"
        display="flex"
        flexDirection="column"
        alignItems="center"
        justifyContent="center"
        gap={8}
      >
        <Flex alignItems="center">
          <Heading as="h2" color="brand.400">
            {code}
          </Heading>
          <Divider orientation="vertical" size="50" variant="solid" mx={2} />
          <Heading as="h1">{t(message.toLocaleLowerCase())}</Heading>
        </Flex>
        <Button as={Link} href="/" _hover={{ textDecoration: "none" }}>
          {t("back to home")}
        </Button>
      </Container>
    </>
  );
};

export default Error;
