import {
  Box,
  Button,
  Flex,
  Icon,
  Table,
  Tbody,
  Td,
  Text,
  Th,
  Thead,
  Tr,
  useColorModeValue,
} from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import dayjs from "dayjs";
import { useCallback, useEffect, useMemo, useState } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";
import { HiOutlineCheck, HiOutlineX } from "react-icons/hi";

import Autocomplete from "@/components/Autocomplete";
import Modal from "@/components/Modal";

import { TIME_FORMAT } from "@/constants/datetime";

import { useGetCashierAttendances } from "@/services/cashierAttendance";
import { useGetCashierStores } from "@/services/store";

import { interpretTotalHours } from "@/utils/time";

import { PageProps } from "@/types";

type ClockType = "checkIn" | "checkOut";

interface FormValues {
  userId: number;
}

type AttendanceModalProps = {
  isOpen: boolean;
  onClose: () => void;
};

const AttendanceModal = ({ isOpen, onClose }: AttendanceModalProps) => {
  const { t } = useTranslation();

  const [isSubmitting, setIsSubmitting] = useState(false);
  const [query, setQuery] = useState("");
  const [clockType, setClockType] = useState<ClockType>();
  const [currentTime, setCurrentTime] = useState(
    dayjs().format("YYYY-MM-DD HH:mm:ss"),
  );

  const bgColor = useColorModeValue("white", "gray.700");

  const { storeId, errors: serverErrors } = usePage<PageProps>().props;
  const {
    register,
    reset,
    watch,
    setValue,
    handleSubmit: formSubmitHandler,
    formState: { errors },
  } = useForm<FormValues>();

  const userId = watch("userId");

  const {
    data: attendances,
    refetch: refetchAttendances,
    isFetching: isFetchingAttendances,
  } = useGetCashierAttendances({
    request: {
      filter: {
        eq: {
          userId: userId,
          storeId: storeId ?? undefined,
        },
      },
      sort: {
        checkInAt: "desc",
      },
    },
    query: {
      keepPreviousData: false,
      staleTime: 0,
      enabled: !!userId && !!storeId,
    },
  });

  const { data: store, isFetching: isFetchingEmployees } = useGetCashierStores({
    request: {
      filter: {
        eq: {
          id: storeId ?? undefined,
        },
      },
      size: 1,
      show: "active",
      include: ["users"],
      only: ["users.id", "users.fullname", "users.identityNumber"],
    },
    query: {
      enabled: !!storeId,
    },
  });

  const isClockedIn = useMemo(() => {
    return !!attendances?.[0]?.checkInAt;
  }, [attendances]);

  const isClockedOut = useMemo(() => {
    return !!attendances?.[0]?.checkOutAt;
  }, [attendances]);

  const isClockedOutToday = useMemo(() => {
    return attendances?.some((attendance) =>
      dayjs(attendance.checkOutAt).isSame(dayjs(), "day"),
    );
  }, [attendances]);

  const employeeOptions = useMemo(() => {
    const users = store?.[0]?.users;

    if (!query || !users) {
      return [];
    }

    const filteredUsers = users.filter(
      (user) =>
        Number(user.id) === Number(query) || user.identityNumber === query,
    );

    return filteredUsers.map((user) => ({
      label: `${user.id} | ${user.fullname} | ${user.identityNumber}`,
      value: `ID-${user.id}`,
    }));
  }, [store, query]);

  const handleSubmit = useCallback(
    (values: FormValues) => {
      if (!clockType) {
        return;
      }

      setIsSubmitting(true);

      const url =
        clockType === "checkIn"
          ? "/cashier/attendances/check-in"
          : "/cashier/attendances/check-out";

      router.post(
        url,
        { ...values },
        {
          onFinish: () => {
            setIsSubmitting(false);
            setClockType(undefined);
            refetchAttendances();
          },
        },
      );
    },
    [clockType, refetchAttendances],
  );

  useEffect(() => {
    if (isOpen) {
      reset();
      setQuery("");
    }
  }, [isOpen, reset]);

  useEffect(() => {
    let intervalId: NodeJS.Timeout;

    if (isOpen) {
      intervalId = setInterval(() => {
        setCurrentTime(dayjs().format("YYYY-MM-DD HH:mm:ss"));
      }, 1000);
    }

    return () => {
      if (intervalId) {
        clearInterval(intervalId);
      }
    };
  }, [isOpen]);

  return (
    <Modal
      headerContainer={{
        children: (
          <Flex justifyContent="space-between" alignItems="center" pr={6}>
            {t("time clock")}
            <Text fontSize="xs" fontWeight="normal">
              {currentTime}
            </Text>
          </Flex>
        ),
      }}
      size="2xl"
      isOpen={isOpen}
      onClose={onClose}
    >
      <Flex
        as="form"
        direction="column"
        gap={4}
        onSubmit={formSubmitHandler(handleSubmit)}
        autoComplete="off"
        noValidate
      >
        <Autocomplete
          labelText={t("employee attendance")}
          placeholder={t("employee attendance placeholder")}
          options={employeeOptions}
          isLoading={isFetchingEmployees}
          onChangeDebounce={(value) => setQuery(String(value))}
          errorText={errors.userId?.message || serverErrors?.userId}
          value={query}
          {...register("userId", {
            required: t("validation field required"),
            onChange: (e) => setValue("userId", e.target.value.split("-")[1]),
          })}
          freeMode
          isRequired
        />
        {isClockedOutToday && (
          <Box maxH="200px" overflowY="auto" fontSize="xs">
            <Table>
              <Thead pos="sticky" top={0} bg={bgColor}>
                <Tr>
                  <Th py={4}>{t("clock in")}</Th>
                  <Th py={4}>{t("clock out")}</Th>
                  <Th py={4}>{t("date")}</Th>
                  <Th py={4}>{t("work hour")}</Th>
                </Tr>
              </Thead>
              <Tbody>
                {attendances?.map((attendance) => (
                  <Tr key={attendance.id}>
                    <Td py={2}>
                      {dayjs(attendance.checkInAt).format(TIME_FORMAT)}
                    </Td>
                    <Td py={2}>
                      {attendance?.checkOutAt
                        ? dayjs(attendance.checkOutAt).format(TIME_FORMAT)
                        : "-"}
                    </Td>
                    <Td py={2}>{dayjs(attendance.checkInAt).format("LL")}</Td>
                    <Td py={2}>{interpretTotalHours(attendance.totalHours)}</Td>
                  </Tr>
                ))}
              </Tbody>
            </Table>
          </Box>
        )}
        <Flex gap={4} mt={4} w="full">
          <Button
            type="submit"
            variant="outline"
            colorScheme="brand"
            aria-label={t("clock in")}
            flexGrow={1}
            flexDirection="column"
            alignItems="center"
            justifyContent="center"
            gap={2}
            p={2}
            h={28}
            w={24}
            isLoading={
              (isSubmitting && clockType === "checkIn") ||
              (isFetchingAttendances && clockType === "checkIn")
            }
            isDisabled={!userId || (isClockedIn && !isClockedOut)}
            onClick={() => setClockType("checkIn")}
          >
            <Icon as={HiOutlineCheck} boxSize={10} />
            <Text
              whiteSpace="pre-wrap"
              wordBreak="break-word"
              fontSize="sm"
              lineHeight="short"
              textAlign="center"
            >
              {t("clock in")}
            </Text>
          </Button>
          <Button
            alignSelf="flex-start"
            type="submit"
            variant="outline"
            colorScheme="red"
            aria-label={t("clock out")}
            flexGrow={1}
            flexDirection="column"
            alignItems="center"
            justifyContent="center"
            gap={2}
            p={2}
            h={28}
            w={24}
            isLoading={
              (isSubmitting && clockType === "checkOut") ||
              (isFetchingAttendances && clockType === "checkOut")
            }
            isDisabled={
              !userId ||
              (!isClockedIn && !isClockedOut) ||
              (isClockedIn && isClockedOut)
            }
            onClick={() => setClockType("checkOut")}
          >
            <Icon as={HiOutlineX} boxSize={10} />
            <Text
              whiteSpace="pre-wrap"
              wordBreak="break-word"
              fontSize="sm"
              lineHeight="short"
              textAlign="center"
            >
              {t("clock out")}
            </Text>
          </Button>
        </Flex>
      </Flex>
    </Modal>
  );
};

export default AttendanceModal;
