import { Card, CardBody, CardHeader, Heading, Text } from "@chakra-ui/react";
import { Trans, useTranslation } from "react-i18next";

interface WidgetCreditProps {
  totalCredit: number;
}

const WidgetCredit = ({ totalCredit }: WidgetCreditProps) => {
  const { t } = useTranslation();

  return (
    <Card minH={144}>
      <CardHeader>
        <Heading size="sm">{t("credit")}</Heading>
      </CardHeader>
      <CardBody fontSize="sm" paddingTop={0} alignContent="center">
        <Text>
          <Trans i18nKey="credit widget text" values={{ total: totalCredit }} />
        </Text>
      </CardBody>
    </Card>
  );
};

export default WidgetCredit;
