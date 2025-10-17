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
import dayjs from "dayjs";
import { useMemo } from "react";
import { useTranslation } from "react-i18next";

import { useGetWorkHours } from "@/services/timeReports";

import { toDayjs } from "@/utils/datetime";
import { interpretTotalHours } from "@/utils/time";

const WidgetWorkHours = () => {
  const { t } = useTranslation();

  const workHours = useGetWorkHours({
    request: {
      filter: {
        between: {
          date: [
            toDayjs().startOf("month").toISOString(),
            toDayjs().endOf("month").toISOString(),
          ],
        },
      },
      only: ["date", "totalHours", "unapprovedHours"],
      size: -1,
    },
  });

  const data = useMemo(() => {
    const result = {
      "today": {
        total: 0,
        unapproved: 0,
        quarters: 0,
      },
      "this week": {
        total: 0,
        unapproved: 0,
        quarters: 0,
      },
      "this month": {
        total: 0,
        unapproved: 0,
        quarters: 0,
      },
    };

    const timeUnits = {
      "today": "day",
      "this week": "week",
      "this month": "month",
    };

    workHours.data?.forEach((item) => {
      Object.entries(timeUnits).forEach(([time, unit]) => {
        if (toDayjs(item.date).isSame(toDayjs(), unit as dayjs.OpUnitType)) {
          result[time as keyof typeof timeUnits].total += item.totalHours;
          result[time as keyof typeof timeUnits].quarters +=
            item.totalHours * 4;
          result[time as keyof typeof timeUnits].unapproved +=
            item.unapprovedHours;
        }
      });
    });

    return result;
  }, [workHours.data]);

  return (
    <Card minH={330}>
      <CardHeader>
        <Heading size="sm" mb={2}>
          {t("work hours")}
        </Heading>
        <Text fontSize="sm">{t("work hours widget text")}</Text>
      </CardHeader>
      <CardBody fontSize="sm">
        <Table>
          <Thead>
            <Tr>
              <Th>{t("time")}</Th>
              <Th>{t("approved")}</Th>
              <Th>{t("unapproved")}</Th>
              <Th>{t("quarters")}</Th>
            </Tr>
          </Thead>
          <Tbody>
            {Object.entries(data).map(([time, values]) => (
              <Tr key={time}>
                <Td>{t(time)}</Td>
                <Td>{interpretTotalHours(values.total)}</Td>
                <Td>{interpretTotalHours(values.unapproved)}</Td>
                <Td>{Math.ceil(values.quarters)}</Td>
              </Tr>
            ))}
          </Tbody>
        </Table>
      </CardBody>
    </Card>
  );
};

export default WidgetWorkHours;
