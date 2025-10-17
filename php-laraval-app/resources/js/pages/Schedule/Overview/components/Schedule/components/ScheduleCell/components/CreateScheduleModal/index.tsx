import { Box, Button, Flex } from "@chakra-ui/react";
import { Link } from "@inertiajs/react";
import { t } from "i18next";
import { Trans } from "react-i18next";

import Modal from "@/components/Modal";

import Team from "@/types/team";

import { formatTime } from "@/utils/datetime";

interface CreateScheduleModalProps {
  isOpen: boolean;
  onClose: () => void;
  startAt: string;
  endAt?: string;
  team: Team;
}

const CreateScheduleModal = ({
  isOpen,
  onClose,
  startAt,
  endAt,
  team,
}: CreateScheduleModalProps) => {
  return (
    <Modal isOpen={isOpen} onClose={onClose} size="md">
      <Box>
        {endAt ? (
          <Trans
            i18nKey="modal create schedule body"
            values={{
              startAt: formatTime(startAt),
              endAt: formatTime(endAt),
              team: team.name,
            }}
          />
        ) : (
          <Trans
            i18nKey="modal create schedule without endAt"
            values={{
              startAt: formatTime(startAt),
              team: team.name,
            }}
          />
        )}
      </Box>
      <Flex justifyContent="center" mt={8} gap={6}>
        <Link
          href={
            endAt
              ? `/customers/subscriptions/wizard?teamId=${team.id}&startAt=${startAt}&endAt=${endAt}`
              : `/customers/subscriptions/wizard?teamId=${team.id}&startAt=${startAt}`
          }
        >
          <Button>{t("private")}</Button>
        </Link>
        <Link
          href={
            endAt
              ? `/companies/subscriptions/wizard?teamId=${team.id}&startAt=${startAt}&endAt=${endAt}`
              : `/companies/subscriptions/wizard?teamId=${team.id}&startAt=${startAt}`
          }
        >
          <Button>{t("company")}</Button>
        </Link>
      </Flex>
    </Modal>
  );
};

export default CreateScheduleModal;
