import {
  Button,
  Link as ChakraLink,
  Flex,
  Icon,
  Spacer,
  Text,
  Tooltip,
} from "@chakra-ui/react";
import { Link, usePage } from "@inertiajs/react";
import { QueryKey } from "@tanstack/react-query";
import { useTranslation } from "react-i18next";
import { GoNote } from "react-icons/go";
import { HiOutlineClock, HiOutlineHome } from "react-icons/hi2";
import { LuKey, LuUser, LuUsers } from "react-icons/lu";
import { RiExternalLinkLine } from "react-icons/ri";

import AuthorizationGuard from "@/components/AuthorizationGuard";
import Map from "@/components/Map";
import ScheduleCancelConfirmation from "@/components/ScheduleCancelConfirmation";

import { ServiceMembershipType } from "@/constants/service";

import { usePageModal } from "@/hooks/modal";

import CompanyViewModal from "@/pages/Company/Overview/components/ViewModal";
import CustomerViewModal from "@/pages/Customer/Overview/components/ViewModal";
import useScheduleStore from "@/pages/Schedule/Overview/store";
import { ScheduleOverviewPageProps } from "@/pages/Schedule/Overview/types";

import { queryClient } from "@/services/client";

import Schedule from "@/types/schedule";

import { isReadonly } from "@/utils/schedule";

import { PageProps } from "@/types";

interface InfoPanelInfoProps {
  schedule: Schedule;
  scheduleQueryKey: QueryKey;
  onModalClose: () => void;
  onEdit: () => void;
}

const InfoPanelInfo = ({
  schedule,
  scheduleQueryKey,
  onModalClose,
  onEdit,
}: InfoPanelInfoProps) => {
  const { t } = useTranslation();
  const { modal, openModal, closeModal } = usePageModal<
    unknown,
    "customer" | "cancel"
  >();

  const { creditRefundTimeWindow } =
    usePage<PageProps<ScheduleOverviewPageProps>>().props;

  const updateSchedule = useScheduleStore((state) => state.updateSchedule);

  const getKeyInformation = () => {
    if (!schedule.property?.keyInformation?.keyPlace) {
      return schedule.keyInformation;
    }

    const keyPlace = `${t("key place")} ${
      schedule.property?.keyInformation?.keyPlace ?? ""
    }`;

    return schedule.keyInformation
      ? `${keyPlace}, ${schedule.keyInformation}`
      : keyPlace;
  };

  return (
    <>
      <Flex
        direction={{ base: "column", md: "row" }}
        gap={{ base: 3, md: 6 }}
        mb={8}
      >
        <Flex direction="column" justify="space-between" flex={1} gap={3}>
          <Flex align="center" gap={4}>
            <Tooltip label={t("customer")}>
              <Flex align="center">
                <Icon as={LuUser} />
              </Flex>
            </Tooltip>
            <ChakraLink
              color="blue.300"
              textDecoration="underline"
              fontSize="sm"
              fontWeight="300"
              onClick={() => openModal("customer")}
            >
              {schedule?.user?.fullname ?? ""}
            </ChakraLink>
          </Flex>
          <Flex align="center" gap={4}>
            <Tooltip label={t("address")}>
              <Flex align="center">
                <Icon as={HiOutlineHome} />
              </Flex>
            </Tooltip>
            <Text fontSize="sm" fontWeight="300">
              {schedule.property?.address?.fullAddress ?? ""}
            </Text>
          </Flex>
          <Flex align="center" gap={4}>
            <Tooltip label={t("total quarters")}>
              <Flex align="center">
                <Icon as={HiOutlineClock} />
              </Flex>
            </Tooltip>
            <Text fontSize="sm" fontWeight="300">
              {schedule.quarters} Quarters
            </Text>
          </Flex>
        </Flex>
        <Flex direction="column" justify="space-between" flex={1} gap={3}>
          <Flex align="center" gap={4}>
            <Tooltip label={t("team")}>
              <Flex align="center">
                <Icon as={LuUsers} />
              </Flex>
            </Tooltip>
            <Text fontSize="sm" fontWeight="300">
              {schedule.team?.name ?? ""}
            </Text>
          </Flex>
          <Flex align="center" gap={4}>
            <Tooltip label={t("key information")}>
              <Flex align="center">
                <Icon as={LuKey} />
              </Flex>
            </Tooltip>
            <Text fontSize="sm" fontWeight="300">
              {getKeyInformation()}
            </Text>
          </Flex>
          <Flex align="center" gap={4}>
            <Tooltip label={t("note")}>
              <Flex align="center">
                <Icon as={GoNote} />
              </Flex>
            </Tooltip>
            <Text fontSize="sm" fontWeight="300">
              {schedule.note}
            </Text>
          </Flex>
        </Flex>
      </Flex>
      {!!schedule.property?.address?.latitude &&
        !!schedule.property?.address?.longitude && (
          <Map
            height="300px"
            rounded="md"
            center={{
              lat: schedule.property?.address?.latitude ?? 0,
              lng: schedule.property?.address?.longitude ?? 0,
            }}
            markers={[
              {
                position: {
                  lat: schedule.property?.address?.latitude ?? 0,
                  lng: schedule.property?.address?.longitude ?? 0,
                },
                tooltipContainer: {
                  direction: "top",
                  offset: [0, -20],
                  permanent: true,
                },
                tooltip: schedule.property?.address?.address ?? "",
              },
            ]}
            mapContainer={{ zoomControl: false, attributionControl: false }}
          />
        )}
      {!isReadonly(schedule) && (
        <Flex align="center" mt={8}>
          <AuthorizationGuard permissions="schedules cancel">
            <Button
              variant="outline"
              colorScheme="red"
              fontSize="sm"
              onClick={() => openModal("cancel")}
            >
              {t("cancel this schedule")}
            </Button>
          </AuthorizationGuard>
          <Spacer />
          <AuthorizationGuard permissions="schedules update">
            <Button fontSize="sm" onClick={onEdit}>
              {t("edit schedule")}
            </Button>
          </AuthorizationGuard>
        </Flex>
      )}

      {schedule.status === "done" && schedule.hasDeviation && (
        <Flex mt={8} justify="flex-end">
          <AuthorizationGuard permissions="deviations index">
            <Button
              as={Link}
              href={`/deviations?scheduleCleaningId.eq=${schedule.id}`}
              variant="outline"
              fontSize="sm"
              leftIcon={<Icon as={RiExternalLinkLine} boxSize={4} />}
            >
              {t("deviation")}
            </Button>
          </AuthorizationGuard>
        </Flex>
      )}

      <CustomerViewModal
        data={schedule?.user}
        isOpen={
          modal === "customer" &&
          schedule.customer?.membershipType === ServiceMembershipType.PRIVATE
        }
        onClose={closeModal}
      />

      <CompanyViewModal
        companyId={schedule.customerId}
        user={schedule?.user}
        isOpen={
          modal === "customer" &&
          schedule.customer?.membershipType === ServiceMembershipType.COMPANY
        }
        onClose={closeModal}
      />

      <ScheduleCancelConfirmation
        schedule={schedule}
        creditRefundTimeWindow={creditRefundTimeWindow}
        isOpen={modal === "cancel"}
        onClose={closeModal}
        onSuccess={(data, response) => {
          queryClient.setQueryData(scheduleQueryKey, response);
          updateSchedule(data);
          onModalClose();
        }}
      />
    </>
  );
};

export default InfoPanelInfo;
