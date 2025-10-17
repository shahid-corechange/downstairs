import {
  Card,
  CardBody,
  CardHeader,
  Heading,
  Table,
  Tbody,
  Td,
  Th,
  Thead,
  Tr,
} from "@chakra-ui/react";
import dayjs from "dayjs";
import { useMemo } from "react";
import { useTranslation } from "react-i18next";

import { usePageModal } from "@/hooks/modal";

import { useGetScheduleItems } from "@/services/scheduleItem";

import { toDayjs } from "@/utils/datetime";

import DetailModal from "./components/DetailModal";

// TODO: update this after BE is ready
const WidgetAddOn = () => {
  const { t } = useTranslation();

  const { modal, openModal, closeModal } = usePageModal<unknown, "addon">();

  const scheduleItem = useGetScheduleItems({
    request: {
      size: -1,
      filter: {
        in: { "schedule.status": ["booked", "progress", "done"] },
        between: {
          "schedule.startAt": [
            toDayjs().startOf("month").toISOString(),
            toDayjs().endOf("month").toISOString(),
          ],
        },
        eq: {
          "item.categoryId": 1,
        },
      },
      include: ["schedule", "item"],
      only: [
        "id",
        "paymentMethod",
        "schedule.startAt",
        "schedule.endAt",
        "schedule.status",
        "item.name",
        "item.creditPrice",
        "item.priceWithVat",
      ],
      sort: { "schedule.startAt": "asc" },
    },
  });

  const data = useMemo(() => {
    const result = {
      "today": {
        addon: new Set(),
        credit: 0,
        invoice: 0,
        total: 0,
      },
      "this week": {
        addon: new Set(),
        credit: 0,
        invoice: 0,
        total: 0,
      },
      "this month": {
        addon: new Set(),
        credit: 0,
        invoice: 0,
        total: 0,
      },
    };

    const timeUnits = {
      "today": "day",
      "this week": "week",
      "this month": "month",
    };

    scheduleItem.data?.forEach((item) => {
      Object.entries(timeUnits).forEach(([time, unit]) => {
        if (
          toDayjs(item?.schedule?.startAt).isSame(
            toDayjs(),
            unit as dayjs.OpUnitType,
          )
        ) {
          result[time as keyof typeof timeUnits].addon.add(item.item?.name);

          if (item.paymentMethod === "credit") {
            result[time as keyof typeof timeUnits].credit += 1;
          }

          if (item.paymentMethod === "invoice") {
            result[time as keyof typeof timeUnits].invoice += 1;
          }

          result[time as keyof typeof timeUnits].total += 1;
        }
      });
    });

    return result;
  }, [scheduleItem.data]);

  return (
    <Card minH={330} onClick={() => openModal("addon")} cursor="pointer">
      <CardHeader>
        <Heading size="sm" mb={2}>
          {t("add on")}
        </Heading>
      </CardHeader>
      <CardBody fontSize="sm">
        <Table>
          <Thead>
            <Tr>
              <Th>{t("time")}</Th>
              <Th>{t("add on")}</Th>
              <Th>{t("credits")}</Th>
              <Th>{t("invoice")}</Th>
              <Th>{t("total")}</Th>
            </Tr>
          </Thead>
          <Tbody>
            {Object.entries(data).map(([time, values]) => (
              <Tr key={time}>
                <Td>{t(time)}</Td>
                <Td>{values.addon.size}</Td>
                <Td>{values.credit}</Td>
                <Td>{values.invoice}</Td>
                <Td>{values.total}</Td>
              </Tr>
            ))}
          </Tbody>
        </Table>
      </CardBody>

      <DetailModal isOpen={modal === "addon"} onClose={closeModal} />
    </Card>
  );
};

export default WidgetAddOn;
