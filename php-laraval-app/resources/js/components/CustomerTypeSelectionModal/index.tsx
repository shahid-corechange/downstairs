import { Button, Flex } from "@chakra-ui/react";
import { useTranslation } from "react-i18next";

import Modal from "@/components/Modal";

type CustomerTypeSelectionModalData = {
  name?: string;
  isNewPage?: boolean;
};

interface CustomerTypeSelectionModalProps {
  data?: CustomerTypeSelectionModalData;
  isOpen: boolean;
  onClose: () => void;
}

const CustomerTypeSelectionModal = ({
  data,
  onClose,
  isOpen,
}: CustomerTypeSelectionModalProps) => {
  const { t } = useTranslation();

  const handleOpenWizard = (type: "private" | "company") => {
    const name = data ? `&name=${data.name}` : "";

    onClose();
    window.open(
      `/cashier/customers/wizard?type=${type}${name}`,
      data?.isNewPage ? "_blank" : "_self",
    );
  };

  return (
    <Modal
      title={t("customer type")}
      isOpen={isOpen}
      onClose={onClose}
      size="md"
    >
      <Flex justifyContent="center" gap={6}>
        <Button onClick={() => handleOpenWizard("private")}>
          {t("private")}
        </Button>
        <Button onClick={() => handleOpenWizard("company")}>
          {t("company")}
        </Button>
      </Flex>
    </Modal>
  );
};

export default CustomerTypeSelectionModal;
