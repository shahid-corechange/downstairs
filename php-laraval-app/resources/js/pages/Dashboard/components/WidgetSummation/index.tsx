import {
  Card,
  CardBody,
  CardHeader,
  Heading,
  Table,
  Tbody,
  Td,
  Text,
  Th,
  Thead,
  Tr,
} from "@chakra-ui/react";
import * as _ from "lodash-es";
import { useTranslation } from "react-i18next";

import { useScheduleSummation } from "@/services/schedule";

import { toDayjs } from "@/utils/datetime";

const WidgetSummation = () => {
  const { t } = useTranslation();
  const scheduleSummations = useScheduleSummation([
    toDayjs().startOf("day").toISOString(),
    toDayjs().endOf("day").toISOString(),
  ]);

  return (
    <Card minH={300}>
      <CardHeader>
        <Heading size="sm">{t("summation")}</Heading>
      </CardHeader>
      <CardBody fontSize="sm">
        <Table>
          <Thead>
            <Tr>
              <Th>{t("type")}</Th>
              <Th>{t("amount")}</Th>
              <Th>{t("size")}</Th>
            </Tr>
          </Thead>
          <Tbody>
            {scheduleSummations.data?.map((data, index) => (
              <Tr key={index}>
                <Td>{data.type}</Td>
                <Td>{data.amount}</Td>
                <Td>
                  {data.size !== undefined
                    ? `${_.round(data.size, 2)} ${data.unit}`
                    : "-"}
                </Td>
              </Tr>
            ))}
          </Tbody>
        </Table>
        {!scheduleSummations.data && (
          <Text textAlign="center" mt={4}>
            {t("loading") + "..."}
          </Text>
        )}
      </CardBody>
    </Card>
  );
};

export default WidgetSummation;
