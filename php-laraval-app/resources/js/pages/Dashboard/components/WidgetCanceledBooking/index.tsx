import {
  Card,
  CardBody,
  CardHeader,
  Heading,
  Text,
  useDisclosure,
} from "@chakra-ui/react";
import { Trans, useTranslation } from "react-i18next";

import DetailModal from "./components/DetailModal";

interface WidgetCanceledBookingProps {
  canceledByCustomer: number;
  canceledByTeam: number;
  canceledByAdmin: number;
}

const WidgetCanceledBooking = ({
  canceledByCustomer,
  canceledByTeam,
  canceledByAdmin,
}: WidgetCanceledBookingProps) => {
  const { t } = useTranslation();
  const { isOpen, onOpen, onClose } = useDisclosure();

  const hasCancelations =
    canceledByCustomer > 0 || canceledByTeam > 0 || canceledByAdmin > 0;

  return (
    <div
      onClick={onOpen}
      style={{
        display: "contents",
        flex: 1,
        cursor: "pointer",
      }}
    >
      <Card
        minH={143}
        textAlign="left"
        backgroundColor={hasCancelations ? "red.300" : undefined}
        transition="background-color 0.2s ease-in-out"
      >
        <CardHeader>
          <Heading size="sm">{t("canceled bookings")}</Heading>
        </CardHeader>
        <CardBody fontSize="sm" paddingTop={0} alignContent="center">
          <Text>
            <Trans
              i18nKey="canceled booking widget text"
              values={{
                total: canceledByCustomer,
                userType: t("customer"),
              }}
            />
          </Text>
          <Text>
            <Trans
              i18nKey="canceled booking widget text"
              values={{
                total: canceledByTeam,
                userType: t("employee"),
              }}
            />
          </Text>
          <Text>
            <Trans
              i18nKey="canceled booking widget text"
              values={{
                total: canceledByAdmin,
                userType: t("admin"),
              }}
            />
          </Text>
        </CardBody>
      </Card>

      <DetailModal isOpen={isOpen} onClose={onClose} />
    </div>
  );
};

export default WidgetCanceledBooking;
