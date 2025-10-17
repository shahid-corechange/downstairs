import {
  Badge,
  Flex,
  Heading,
  Icon,
  Spacer,
  TabPanel,
  TabPanelProps,
  Tag,
  TagLabel,
  Tooltip,
} from "@chakra-ui/react";
import { QueryKey } from "@tanstack/react-query";
import { useTranslation } from "react-i18next";
import { HiOutlineLockClosed } from "react-icons/hi2";

import { ModalExpansion } from "@/components/Modal/types";

import { ServiceMembershipType } from "@/constants/service";

import { usePageModal } from "@/hooks/modal";

import CompanyViewModal from "@/pages/Company/Overview/components/ViewModal";
import CustomerViewModal from "@/pages/Customer/Overview/components/ViewModal";
import useScheduleStore from "@/pages/Schedule/Overview/store";

import { queryClient } from "@/services/client";

import Schedule from "@/types/schedule";

import { toDayjs } from "@/utils/datetime";

import InfoPanelForm from "./components/Form";
import InfoPanelInfo from "./components/Info";
import { checkProductEqual } from "./helpers";

const statusColors: Record<Schedule["status"], string> = {
  booked: "gray",
  cancel: "red",
  change: "yellow",
  done: "green",
  draft: "gray",
  invoiced: "green",
  pending: "gray",
  progress: "blue",
};

const typeColors: Record<string, string> = {
  company: "blue",
  private: "orange",
};

interface InfoPanelProps extends TabPanelProps {
  schedule: Schedule;
  scheduleQueryKey: QueryKey;
  onModalClose: () => void;
  onModalExpansion: (expansion: ModalExpansion) => void;
  onModalShrink: () => void;
}

const InfoPanel = ({
  schedule,
  scheduleQueryKey,
  onModalClose,
  onModalExpansion,
  onModalShrink,
  ...props
}: InfoPanelProps) => {
  const { t } = useTranslation();

  const updateSchedule = useScheduleStore((state) => state.updateSchedule);

  const { modal, openModal, closeModal } = usePageModal<unknown, "customer">();

  const startAt = toDayjs(schedule.startAt);

  return (
    <>
      <TabPanel {...props}>
        <Flex direction="column" justify="space-between" gap={3} mb={8}>
          <Flex align="center" gap={4}>
            <Heading size="md" color="brand.500" _dark={{ color: "brand.100" }}>
              {startAt.format("LLL")}
            </Heading>
            <Spacer />
            {schedule.isFixed && (
              <Tooltip label={t("fixed time")}>
                <Flex align="center">
                  <Icon as={HiOutlineLockClosed} />
                </Flex>
              </Tooltip>
            )}
            {schedule?.subscription?.fixedPriceId && (
              <Tooltip label={t("fixed price")}>
                <Badge
                  colorScheme="blue"
                  variant="subtle"
                  onClick={() => openModal("customer")}
                  cursor="pointer"
                >
                  {t("fixed price")}
                </Badge>
              </Tooltip>
            )}
            <Tooltip label={t("type")}>
              <Badge
                colorScheme={
                  typeColors[schedule.customer?.membershipType ?? ""]
                }
                variant="subtle"
              >
                {t(schedule.customer?.membershipType ?? "")}
              </Badge>
            </Tooltip>
            <Tooltip label={t("status")}>
              <Badge
                colorScheme={statusColors[schedule.status]}
                variant="subtle"
              >
                {t(schedule.status)}
              </Badge>
            </Tooltip>
          </Flex>
          <Heading
            size="sm"
            color="gray.600"
            fontWeight="medium"
            _dark={{ color: "gray.300" }}
          >
            {schedule?.service?.name ?? ""}
          </Heading>
          <Flex gap={2}>
            {(schedule?.items ?? []).map((item) => (
              <Tag key={item.itemableId} size="sm">
                <TagLabel>{item.item?.name ?? ""}</TagLabel>
              </Tag>
            ))}
          </Flex>
        </Flex>
        <InfoPanelInfo
          schedule={schedule}
          scheduleQueryKey={scheduleQueryKey}
          onModalClose={onModalClose}
          onEdit={() =>
            onModalExpansion({
              content: (
                <InfoPanelForm
                  schedule={schedule}
                  onCancel={onModalShrink}
                  onSuccess={(data, response) => {
                    queryClient.setQueryData(scheduleQueryKey, response);

                    const isProductEqual = checkProductEqual(
                      schedule.items ?? [],
                      data.items ?? [],
                    );

                    if (
                      schedule.startAt !== data.startAt ||
                      schedule.endAt !== data.endAt ||
                      schedule.teamId !== data.teamId ||
                      schedule.quarters !== data.quarters ||
                      !isProductEqual
                    ) {
                      updateSchedule(data);
                    }
                  }}
                />
              ),
              title: t("edit schedule"),
            })
          }
        />
      </TabPanel>

      <CustomerViewModal
        data={schedule?.user}
        activeTab={7}
        isOpen={
          modal === "customer" &&
          schedule.customer?.membershipType === ServiceMembershipType.PRIVATE
        }
        onClose={closeModal}
      />

      <CompanyViewModal
        companyId={schedule.customerId}
        user={schedule?.user}
        activeTab={6}
        isOpen={
          modal === "customer" &&
          schedule.customer?.membershipType === ServiceMembershipType.COMPANY
        }
        onClose={closeModal}
      />
    </>
  );
};

export default InfoPanel;
