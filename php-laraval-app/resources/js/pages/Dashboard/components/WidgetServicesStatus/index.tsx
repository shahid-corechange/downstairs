import {
  Card,
  CardBody,
  CardHeader,
  Grid,
  GridItem,
  Heading,
  Text,
} from "@chakra-ui/react";
import React from "react";
import { Trans, useTranslation } from "react-i18next";

import { ServiceStatus } from "@/types/servicesStatus";

import { humanizeDate } from "@/utils/time";

interface WidgetServicesStatusProps {
  servicesStatus: ServiceStatus[];
}

// list of services that want to be displayed
const includedServices = [
  "SMS",
  "Mail",
  "Fortnox Customer",
  "Fortnox Employee",
];

const WidgetServicesStatus = ({
  servicesStatus,
}: WidgetServicesStatusProps) => {
  const { t } = useTranslation();

  return (
    <Grid
      templateColumns={{
        base: "repeat(1, 1fr)",
        lg: "repeat(3, 1fr)",
        xl: "repeat(3, 1fr)",
      }}
      alignItems="start"
      gap={4}
      mt={8}
    >
      {servicesStatus.reduce<React.ReactNode[]>((acc, service) => {
        if (includedServices.includes(service.name)) {
          acc.push(
            <GridItem key={service.name}>
              <Card>
                <CardHeader>
                  <Heading size="sm">{service.name}</Heading>
                </CardHeader>
                <CardBody fontSize="sm">
                  <Text
                    fontWeight={1000}
                    textColor={service.status === "ok" ? "green" : "red"}
                  >
                    {t(service.status)}
                  </Text>
                  <Trans
                    i18nKey="last checked"
                    values={{
                      date: humanizeDate(service.lastCheckedAt),
                    }}
                  />
                </CardBody>
              </Card>
            </GridItem>,
          );
        }
        return acc;
      }, [])}
    </Grid>
  );
};

export default WidgetServicesStatus;
