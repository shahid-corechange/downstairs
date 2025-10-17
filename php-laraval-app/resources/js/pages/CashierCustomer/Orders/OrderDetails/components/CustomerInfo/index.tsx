import { Card, CardBody, CardHeader, Flex, Heading } from "@chakra-ui/react";
import { useTranslation } from "react-i18next";

import Customer from "@/types/customer";

import InfoRow from "./components/InfoRow";

interface CustomerInfoProps {
  userId: number;
  customer?: Customer;
}

const CustomerInfo = ({ userId, customer }: CustomerInfoProps) => {
  const { t } = useTranslation();

  return (
    <Card>
      <CardHeader>
        <Heading size="sm">{t("customer info")}</Heading>
      </CardHeader>
      <CardBody fontSize="sm">
        <Flex direction="column" gap={4} justifyContent="space-between">
          <InfoRow label={t("id")} value={userId} />
          <InfoRow
            label={t("customer type")}
            value={t(customer?.membershipType || "-")}
          />
          <InfoRow
            label={t("identity number")}
            value={customer?.identityNumber}
          />
          <InfoRow label={t("name")} value={customer?.name} />
          <InfoRow label={t("phone")} value={customer?.formattedPhone1} />
        </Flex>
      </CardBody>
    </Card>
  );
};

export default CustomerInfo;
