import { Card, CardBody, CardHeader, Flex, Heading } from "@chakra-ui/react";
import { useMemo } from "react";
import { useTranslation } from "react-i18next";

import User from "@/types/user";

import InfoRow from "./components/InfoRow";

interface CustomerInfoProps {
  user?: User;
}

const CustomerInfo = ({ user }: CustomerInfoProps) => {
  const { t } = useTranslation();

  const membershipType = useMemo(
    () =>
      user?.customers?.find((customer) => customer.type === "primary")
        ?.membershipType,
    [user],
  );

  return (
    <Card role="region" aria-label={t("customer info")}>
      <CardHeader>
        <Heading as="h2" size="sm">
          {t("customer info")}
        </Heading>
      </CardHeader>
      <CardBody fontSize="sm">
        <Flex direction="column" gap={4} justifyContent="space-between">
          <InfoRow label={t("id")} value={user?.id} />
          <InfoRow
            label={t("customer type")}
            value={t(membershipType || "-")}
          />
          <InfoRow label={t("identity number")} value={user?.identityNumber} />
          <InfoRow label={t("name")} value={user?.fullname} />
          <InfoRow label={t("phone")} value={user?.formattedCellphone} />
        </Flex>
      </CardBody>
    </Card>
  );
};

export default CustomerInfo;
