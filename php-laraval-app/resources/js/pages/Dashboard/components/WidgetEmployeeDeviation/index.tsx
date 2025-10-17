import { Card, CardBody, CardHeader, Heading, Text } from "@chakra-ui/react";
import { Link } from "@inertiajs/react";
import { Trans, useTranslation } from "react-i18next";

import { useGetTotalUnhandledEmployeeDeviations } from "@/services/deviation";

import Deviation from "@/types/deviation";

import { createQueryString } from "@/utils/request";

const WidgetEmployeeDeviation = () => {
  const { t } = useTranslation();
  const totalUnhandledEmployeeDeviations =
    useGetTotalUnhandledEmployeeDeviations();

  const queryString = createQueryString<Deviation>({
    filter: { eq: { isHandled: false } },
  });

  return (
    <Link
      href={`deviations/employee${queryString}`}
      as="button"
      style={{
        display: "contents",
        flex: 1,
      }}
    >
      <Card
        minH={144}
        textAlign="left"
        backgroundColor={
          totalUnhandledEmployeeDeviations.data ? "red.300" : undefined
        }
        transition="background-color 0.2s ease-in-out"
      >
        <CardHeader>
          <Heading size="sm">{t("employees deviations")}</Heading>
        </CardHeader>
        <CardBody fontSize="sm" paddingTop={0} alignContent="center">
          <Text>
            {totalUnhandledEmployeeDeviations.isLoading ? (
              t("please wait")
            ) : (
              <Trans
                i18nKey="employees deviations widget text"
                values={{ total: totalUnhandledEmployeeDeviations.data }}
              />
            )}
          </Text>
        </CardBody>
      </Card>
    </Link>
  );
};

export default WidgetEmployeeDeviation;
