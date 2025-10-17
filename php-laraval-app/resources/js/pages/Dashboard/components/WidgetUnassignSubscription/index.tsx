import { Card, CardBody, CardHeader, Heading, Text } from "@chakra-ui/react";
import { Link } from "@inertiajs/react";
import { Trans, useTranslation } from "react-i18next";

interface WidgetUnassignSubscriptionProps {
  plannedToStartTomorrow: number[];
  plannedToStartNextWeek: number[];
  alreadyPassed: number[];
}

const WidgetUnassignSubscription = ({
  plannedToStartTomorrow,
  plannedToStartNextWeek,
  alreadyPassed,
}: WidgetUnassignSubscriptionProps) => {
  const { t } = useTranslation();
  const totalStartTomorrow = plannedToStartTomorrow.length;
  const totalStartNextWeek = plannedToStartNextWeek.length;
  const totalAlreadyPassed = alreadyPassed.length;

  return (
    <Link
      href="/unassign-subscriptions"
      as="button"
      style={{
        display: "contents",
        flex: 1,
      }}
    >
      <Card minH={143} textAlign="left">
        <CardHeader>
          <Heading size="sm">{t("unassign subscriptions")}</Heading>
        </CardHeader>
        <CardBody fontSize="sm" paddingTop={0} alignContent="center">
          <Text>
            <Trans
              i18nKey="unassign subscription tomorrow widget text"
              values={{
                total: totalStartTomorrow,
              }}
            />
          </Text>
          <Text>
            <Trans
              i18nKey="unassign subscription next week widget text"
              values={{
                total: totalStartNextWeek,
              }}
            />
          </Text>
          <Text>
            <Trans
              i18nKey="unassign subscription passed widget text"
              values={{
                total: totalAlreadyPassed,
              }}
            />
          </Text>
        </CardBody>
      </Card>
    </Link>
  );
};

export default WidgetUnassignSubscription;
