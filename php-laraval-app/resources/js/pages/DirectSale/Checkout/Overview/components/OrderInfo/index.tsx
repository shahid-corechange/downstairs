import {
  Alert,
  AlertIcon,
  Card,
  CardBody,
  CardHeader,
  Flex,
  Grid,
  GridItem,
  Heading,
  Text,
} from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

import { useGetCashierStores } from "@/services/store";

import { PageProps } from "@/types";

interface InfoRowProps {
  label: string;
  value: string | undefined;
}

const InfoRow = ({ label, value }: InfoRowProps) => (
  <Grid gridTemplateColumns="repeat(1, 1fr 2fr)" gap={4}>
    <GridItem alignContent="center">
      <Text fontSize="sm">{label}</Text>
    </GridItem>
    <GridItem alignContent="center">
      <Text fontSize="sm">{value || "-"}</Text>
    </GridItem>
  </Grid>
);

const OrderInfo = () => {
  const { t } = useTranslation();
  const { user, storeId } = usePage<PageProps>().props;

  const {
    data: store,
    isFetching,
    isError,
  } = useGetCashierStores({
    request: {
      filter: {
        eq: {
          id: storeId ?? undefined,
        },
      },
      size: 1,
    },
  });

  if (isError) {
    return (
      <Alert status="error">
        <AlertIcon />
        {t("Failed to load store information")}
      </Alert>
    );
  }

  return (
    <Card>
      <CardHeader>
        <Heading size="sm">{t("order info")}</Heading>
      </CardHeader>
      <CardBody fontSize="sm">
        {isFetching ? (
          t("loading") + "..."
        ) : (
          <Flex direction="column" gap={4} justifyContent="space-between">
            <InfoRow label={t("store")} value={store?.[0]?.name} />
            <InfoRow label={t("sales")} value={user?.fullname} />
          </Flex>
        )}
      </CardBody>
    </Card>
  );
};

export default OrderInfo;
