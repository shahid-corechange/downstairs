import { Card, CardBody, CardHeader, Heading, Text } from "@chakra-ui/react";
import { Link } from "@inertiajs/react";
import { Trans, useTranslation } from "react-i18next";

import { useGetTotalUnhandledDeviations } from "@/services/deviation";

import ScheduleDeviation from "@/types/scheduleDeviation";

import { createQueryString } from "@/utils/request";

const WidgetDeviation = () => {
  const { t } = useTranslation();
  const totalUnhandledDeviations = useGetTotalUnhandledDeviations();

  const queryString = createQueryString<ScheduleDeviation>({
    filter: { eq: { isHandled: false } },
  });

  return (
    <Link
      href={`deviations${queryString}`}
      as="button"
      style={{
        display: "contents",
        flex: 1,
      }}
    >
      <Card
        minH={144}
        textAlign="left"
        backgroundColor={totalUnhandledDeviations.data ? "red.300" : undefined}
        transition="background-color 0.2s ease-in-out"
      >
        <CardHeader>
          <Heading size="sm">{t("deviations")}</Heading>
        </CardHeader>
        <CardBody fontSize="sm" paddingTop={0} alignContent="center">
          <Text>
            {totalUnhandledDeviations.isLoading ? (
              t("please wait")
            ) : (
              <Trans
                i18nKey="deviations widget text"
                values={{ total: totalUnhandledDeviations.data }}
              />
            )}
          </Text>
        </CardBody>
      </Card>
    </Link>
  );
};

export default WidgetDeviation;
