import { Card, CardBody, CardHeader, Heading, Text } from "@chakra-ui/react";
import { Link } from "@inertiajs/react";
import { Trans, useTranslation } from "react-i18next";

interface WidgetFortnoxProps {
  unsyncData: number;
}

const WidgetFortnox = ({ unsyncData }: WidgetFortnoxProps) => {
  const { t } = useTranslation();

  return (
    <Link
      href="/fortnox/sync"
      method="post"
      as="button"
      style={{
        display: "contents",
        flex: 1,
      }}
    >
      <Card
        minH={144}
        bg="yellow.400"
        textAlign="left"
        transition="background-color 0.2s ease-in-out"
        _dark={{ bg: "yellow.500" }}
      >
        <CardHeader>
          <Heading size="sm">{t("fortnox")}</Heading>
        </CardHeader>
        <CardBody fontSize="sm" paddingTop={0} alignContent="center">
          <Text>
            <Trans
              i18nKey="fortnox widget text"
              values={{ total: unsyncData }}
            />
          </Text>
        </CardBody>
      </Card>
    </Link>
  );
};

export default WidgetFortnox;
