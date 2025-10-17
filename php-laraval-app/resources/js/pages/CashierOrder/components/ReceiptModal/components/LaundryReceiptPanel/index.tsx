import { Box, Button, Flex, TabPanel, TabPanelProps } from "@chakra-ui/react";
import { useRef } from "react";
import { useTranslation } from "react-i18next";
import { useReactToPrint } from "react-to-print";

import LaundryReceipt from "@/components/LaundryReceipt";

import { LaundryOrder } from "@/types/laundryOrder";

interface LaundryPanelProps extends TabPanelProps {
  laundryOrder?: LaundryOrder;
  onClose: () => void;
}

const LaundryPanel = ({
  laundryOrder,
  onClose,
  ...props
}: LaundryPanelProps) => {
  const { t } = useTranslation();
  const printRef = useRef<HTMLDivElement>(null);
  const handlePrint = useReactToPrint({
    pageStyle: "",
    contentRef: printRef,
    documentTitle: "KASSAN - Downstairs APP",
  });

  if (!laundryOrder) {
    return;
  }

  return (
    <TabPanel {...props}>
      <Flex direction="column" justify="space-between" gap={5} maxH="xl">
        <Box
          overflow="auto"
          p={4}
          border="1px"
          borderColor="gray.300"
          _dark={{
            borderColor: "gray.600",
          }}
          borderRadius="md"
        >
          <LaundryReceipt ref={printRef} laundryOrder={laundryOrder} />
        </Box>

        <Flex justify="flex-end" gap={4}>
          <Button colorScheme="gray" onClick={onClose} fontSize="sm">
            {t("close")}
          </Button>
          <Button onClick={() => handlePrint()} fontSize="sm">
            {t("print")}
          </Button>
        </Flex>
      </Flex>
    </TabPanel>
  );
};

export default LaundryPanel;
