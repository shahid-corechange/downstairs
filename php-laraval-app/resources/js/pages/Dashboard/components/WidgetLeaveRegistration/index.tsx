import { Card, CardBody, CardHeader, Heading, Text } from "@chakra-ui/react";
import { Link } from "@inertiajs/react";
import { Trans, useTranslation } from "react-i18next";

import { createQueryString } from "@/utils/request";

interface WidgetLeaveRegistrationProps {
  leaveRegistrations: number[];
}

const WidgetLeaveRegistration = ({
  leaveRegistrations,
}: WidgetLeaveRegistrationProps) => {
  const { t } = useTranslation();
  const totalLeaveRegistrations = leaveRegistrations.length;
  const queryString = createQueryString({
    size: -1,
    filter: { in: { id: leaveRegistrations } },
  });

  return (
    <Link
      href={`/leave-registrations${queryString}`}
      as="button"
      style={{
        display: "contents",
        flex: 1,
      }}
    >
      <Card minH={144} textAlign="left">
        <CardHeader>
          <Heading size="sm">
            {t("leave registration needs to reschedule")}
          </Heading>
        </CardHeader>
        <CardBody fontSize="sm" paddingTop={0} alignContent="center">
          <Text>
            <Trans
              i18nKey="leave registration widget text"
              values={{
                total: totalLeaveRegistrations,
              }}
            />
          </Text>
        </CardBody>
      </Card>
    </Link>
  );
};

export default WidgetLeaveRegistration;
