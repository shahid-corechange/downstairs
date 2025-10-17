import {
  Button,
  Checkbox,
  Divider,
  Flex,
  Heading,
  Table,
  Tbody,
  Td,
  Text,
  Th,
  Thead,
  Tr,
  createStandaloneToast,
} from "@chakra-ui/react";
import { useEffect, useMemo, useState } from "react";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import Modal from "@/components/Modal";

import { NewAddon } from "../../types";

interface UseCreditModalProps {
  addons: NewAddon[];
  userCredit: number;
  refundCredit: number;
  isOpen: boolean;
  handleSubmit: (onSettled?: () => void, selectedAddons?: number[]) => void;
  onClose: () => void;
}

const UseCreditModal = ({
  addons,
  userCredit,
  refundCredit,
  isOpen,
  handleSubmit,
  onClose,
}: UseCreditModalProps) => {
  const { t } = useTranslation();

  const { toast } = createStandaloneToast();

  const [isLoading, setIsLoading] = useState(false);
  const [selectedAddons, setSelectedAddons] = useState<number[]>([]);

  const handleSelectAddon = (addonId: number) => {
    setSelectedAddons((prev) =>
      prev.includes(addonId)
        ? prev.filter((id) => id !== addonId)
        : [...prev, addonId],
    );
  };

  const handleSelectAll = () => {
    const hasUncheckedAddons = selectedAddons.length !== addons.length;
    setSelectedAddons(
      hasUncheckedAddons ? addons.map((addon) => addon.addonId) : [],
    );
  };

  const totalUseCredit = useMemo(() => {
    return selectedAddons.reduce(
      (acc, addonId) =>
        acc +
        (addons.find((addon) => addon.addonId === addonId)?.creditPrice ?? 0),
      0,
    );
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedAddons]);

  const isCreditInsufficient = totalUseCredit > userCredit;

  const handleUseCredit = () => {
    setIsLoading(true);

    if (isCreditInsufficient) {
      setIsLoading(false);

      toast({
        status: "error",
        variant: "solid",
        position: "top-right",
        title: t("error"),
        description: t("insufficient user credit"),
        containerStyle: {
          fontSize: "sm",
        },
      });

      return;
    }

    handleSubmit(() => setIsLoading(false), selectedAddons);
  };

  useEffect(() => {
    setSelectedAddons(
      addons.filter((addon) => addon.useCredit).map((addon) => addon.addonId),
    );
  }, [addons]);

  return (
    <Modal
      size="lg"
      bodyContainer={{ p: 8 }}
      title={t("use credit")}
      isOpen={isOpen}
      onClose={onClose}
      isCentered={false}
    >
      <Flex direction="column">
        <Alert
          status="info"
          title={t("info")}
          message={t("use credit alert info")}
          fontSize="small"
          mb={6}
        />
        {refundCredit > 0 && (
          <Alert
            status="info"
            title={t("info")}
            message={t("refund credit alert info", {
              amount: refundCredit,
            })}
            fontSize="small"
            mb={6}
          />
        )}

        <Flex direction="row" justify="center" mb={8} gap={8}>
          <Flex direction="column" align="center">
            <Heading
              size="lg"
              textAlign="center"
              color={isCreditInsufficient ? "red.500" : undefined}
            >
              {totalUseCredit}
            </Heading>
            <Text
              textAlign="center"
              color={isCreditInsufficient ? "red.400" : "gray.500"}
            >
              {t("required credits")}
            </Text>
          </Flex>
          <Flex>
            <Divider orientation="vertical" colorScheme="whiteAlpha" />
          </Flex>
          <Flex direction="column" align="center">
            <Heading size="lg" textAlign="center">
              {userCredit}
            </Heading>
            <Text textAlign="center" color="gray.500">
              {t("available credits")}
            </Text>
          </Flex>
        </Flex>

        <Table>
          <Thead>
            <Tr>
              <Th>
                <Checkbox
                  isChecked={selectedAddons.length === addons.length}
                  onChange={handleSelectAll}
                />
              </Th>
              <Th>{t("new addon")}</Th>
              <Th textAlign="right">{t("credit price")}</Th>
            </Tr>
          </Thead>
          <Tbody>
            {addons.map((addon) => (
              <Tr key={addon.addonId}>
                <Td>
                  <Checkbox
                    value={addon.addonId}
                    isChecked={selectedAddons.includes(addon.addonId)}
                    onChange={() => handleSelectAddon(addon.addonId)}
                  />
                </Td>
                <Td fontSize="small">{addon.name}</Td>
                <Td fontSize="small" textAlign="right">
                  {addon.creditPrice}
                </Td>
              </Tr>
            ))}
          </Tbody>
        </Table>

        <Flex justify="right" mt={8} gap={4}>
          <Button colorScheme="gray" fontSize="sm" onClick={onClose}>
            {t("close")}
          </Button>

          <Button
            type="submit"
            fontSize="sm"
            isLoading={isLoading}
            loadingText={t("please wait")}
            onClick={handleUseCredit}
          >
            {t("submit")}
          </Button>
        </Flex>
      </Flex>
    </Modal>
  );
};

export default UseCreditModal;
