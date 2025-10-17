import { Button, Flex, useDisclosure } from "@chakra-ui/react";
import { useTranslation } from "react-i18next";

import AttendanceModal from "./components/AttendanceModal";

const TodayAttendances = () => {
  const { t } = useTranslation();
  const { isOpen, onOpen, onClose } = useDisclosure();

  return (
    <>
      <Flex alignItems="center">
        <Button
          variant="outline"
          colorScheme="gray"
          size="sm"
          fontSize="xs"
          fontWeight="normal"
          onClick={onOpen}
        >
          {t("today attendances")}
        </Button>
      </Flex>
      <AttendanceModal isOpen={isOpen} onClose={onClose} />
    </>
  );
};

export default TodayAttendances;
