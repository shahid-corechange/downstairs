import { Box, Button, Flex, Spinner } from "@chakra-ui/react";
import { useRef } from "react";
import { useTranslation } from "react-i18next";
import { useReactToPrint } from "react-to-print";

import DirectSaleReceipt from "@/components/DirectSaleReceipt";
import Modal from "@/components/Modal";

import { useGetStoreSale } from "@/services/storeSale";

interface ReceiptModalProps {
  storeSaleId?: number;
  isOpen: boolean;
  onClose: () => void;
}

const ReceiptModal = ({ storeSaleId, isOpen, onClose }: ReceiptModalProps) => {
  const { t } = useTranslation();
  const printRef = useRef<HTMLDivElement>(null);
  const handlePrint = useReactToPrint({
    pageStyle: "",
    contentRef: printRef,
    documentTitle: "KASSAN - Downstairs APP",
  });

  const { data: storeSale } = useGetStoreSale(storeSaleId, {
    request: {
      include: ["store", "causer", "products"],
      only: [
        "id",
        "status",
        "paymentMethod",
        "totalToPay",
        "roundedTotalToPay",
        "roundAmount",
        "totalPriceWithVat",
        "totalPriceWithDiscount",
        "totalDiscount",
        "totalVat",
        "createdAt",
        "store.id",
        "store.name",
        "causer.id",
        "causer.fullname",
        "products.name",
        "products.quantity",
        "products.price",
        "products.discount",
        "products.priceWithVat",
        "products.discountAmount",
        "products.vatAmount",
        "products.priceWithDiscount",
        "products.vatGroup",
      ],
    },
    query: {
      enabled: !!storeSaleId && isOpen,
    },
  });

  const handleClose = () => {
    onClose();
  };

  return (
    <Modal
      bodyContainer={{ p: 8 }}
      isOpen={isOpen}
      onClose={handleClose}
      size="lg"
    >
      {storeSale ? (
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
            <DirectSaleReceipt ref={printRef} storeSale={storeSale} />
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
      ) : (
        <Flex h="xs" alignItems="center" justifyContent="center">
          <Spinner size="md" />
        </Flex>
      )}
    </Modal>
  );
};

export default ReceiptModal;
