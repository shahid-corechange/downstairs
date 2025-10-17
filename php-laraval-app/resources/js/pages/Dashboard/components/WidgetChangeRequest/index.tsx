import { Card, CardBody, CardHeader, Heading, Text } from "@chakra-ui/react";
import { Link } from "@inertiajs/react";
import { Trans, useTranslation } from "react-i18next";

import { useGetTotalUnhandledChangeRequests } from "@/services/changeRequest";

import { ScheduleChangeRequest } from "@/types/schedule";

import { createQueryString } from "@/utils/request";

const WidgetChangeRequest = () => {
  const { t } = useTranslation();
  const totalUnhandledChangeRequest = useGetTotalUnhandledChangeRequests();

  const queryString = createQueryString<ScheduleChangeRequest>({
    filter: { eq: { status: "pending" } },
  });

  return (
    <Link
      href={`schedules/change-requests${queryString}`}
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
          totalUnhandledChangeRequest.data ? "red.300" : undefined
        }
        transition="background-color 0.2s ease-in-out"
      >
        <CardHeader>
          <Heading size="sm">{t("change requests")}</Heading>
        </CardHeader>
        <CardBody fontSize="sm" paddingTop={0} alignContent="center">
          <Text>
            {totalUnhandledChangeRequest.isLoading ? (
              t("loading") + "..."
            ) : (
              <Trans
                i18nKey="change request widget text"
                values={{ total: totalUnhandledChangeRequest.data }}
              />
            )}
          </Text>
        </CardBody>
      </Card>
    </Link>
  );
};

export default WidgetChangeRequest;
