import {
  Box,
  Flex,
  Spinner,
  Table,
  Tbody,
  Td,
  Text,
  Th,
  Thead,
  Tr,
} from "@chakra-ui/react";
import dayjs from "dayjs";
import { useTranslation } from "react-i18next";

import Empty from "@/components/Empty";
import Modal from "@/components/Modal";

import { TIME_FORMAT } from "@/constants/datetime";

import { useGetCashierAttendances } from "@/services/cashierAttendance";

import { toDayjs } from "@/utils/datetime";

type AttendanceModalProps = {
  isOpen: boolean;
  onClose: () => void;
};

const AttendanceModal = ({ isOpen, onClose }: AttendanceModalProps) => {
  const { t } = useTranslation();
  const today = toDayjs();

  const { data: attendances, isFetching } = useGetCashierAttendances({
    request: {
      include: ["user"],
      only: [
        "id",
        "user.fullname",
        "user.identityNumber",
        "checkInAt",
        "checkOutAt",
        "totalHours",
      ],
      filter: {
        between: {
          checkInAt: [
            today.startOf("day").toISOString(),
            today.endOf("day").toISOString(),
          ],
        },
      },
      sort: {
        checkInAt: "desc",
      },
      size: -1,
    },
    query: {
      enabled: isOpen,
    },
  });

  return (
    <Modal
      title={t("today attendances")}
      size="2xl"
      isOpen={isOpen}
      onClose={onClose}
    >
      {!isFetching ? (
        <Box maxH="400px" overflowY="auto" fontSize="xs">
          <Table>
            <Thead pos="sticky" top={0}>
              <Tr>
                <Th py={4}>{t("name")}</Th>
                <Th py={4}>{t("identity number")}</Th>
                <Th py={4}>{t("clock in")}</Th>
                <Th py={4}>{t("clock out")}</Th>
              </Tr>
            </Thead>
            <Tbody>
              {attendances?.length === 0 ? (
                <Tr>
                  <Td colSpan={4}>
                    <Empty
                      description={
                        <Text fontSize="sm" color="gray.500">
                          {t("no data")}
                        </Text>
                      }
                    />
                  </Td>
                </Tr>
              ) : (
                attendances?.map((attendance) => (
                  <Tr key={attendance.id}>
                    <Td py={2}>{attendance.user?.fullname || "-"}</Td>
                    <Td py={2}>{attendance.user?.identityNumber || "-"}</Td>
                    <Td py={2}>
                      {dayjs(attendance.checkInAt).format(TIME_FORMAT)}
                    </Td>
                    <Td py={2}>
                      {attendance?.checkOutAt
                        ? dayjs(attendance.checkOutAt).format(TIME_FORMAT)
                        : "-"}
                    </Td>
                  </Tr>
                ))
              )}
            </Tbody>
          </Table>
        </Box>
      ) : (
        <Flex h="xs" alignItems="center" justifyContent="center">
          <Spinner size="md" />
        </Flex>
      )}
    </Modal>
  );
};

export default AttendanceModal;
