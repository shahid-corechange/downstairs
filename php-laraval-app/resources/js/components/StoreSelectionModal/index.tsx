import { Button, Flex, Heading, Spinner } from "@chakra-ui/react";
import { router } from "@inertiajs/react";
import { useCallback, useMemo, useState } from "react";
import { useTranslation } from "react-i18next";

import Modal from "@/components/Modal";

import { useGetCashierStores } from "@/services/store";

import { Store } from "@/types/store";

const handleStoreSelectDefault = (storeId?: number): Promise<boolean> => {
  return new Promise((resolve) => {
    router.post(
      "/change-store",
      {
        storeId,
      },
      {
        onFinish: () => resolve(true),
      },
    );
  });
};

interface StoreSelectionModalProps {
  isOpen: boolean;
  onClose: () => void;
  stores?: Store[];
  from?: "portal" | "login";
  handleStoreSelect?: (storeId?: number) => Promise<boolean>;
}

const StoreSelectionModal = ({
  isOpen,
  onClose,
  stores,
  handleStoreSelect = handleStoreSelectDefault,
  from = "login",
}: StoreSelectionModalProps) => {
  const { t } = useTranslation();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [selectedStoreId, setSelectedStoreId] = useState<number | undefined>(
    undefined,
  );
  const { data: storesDefault, isLoading: isLoading } = useGetCashierStores({
    query: {
      enabled: !!isOpen,
    },
    request: {
      only: ["id", "name"],
    },
  });

  const storesToShow = useMemo(
    () => stores || storesDefault || [],
    [stores, storesDefault],
  );

  const handleOnClick = useCallback(
    async (storeId?: number) => {
      try {
        setIsSubmitting(true);
        await handleStoreSelect(storeId);
      } finally {
        setIsSubmitting(false);
        onClose();
      }
    },
    [handleStoreSelect, onClose],
  );

  return (
    <Modal size="md" isOpen={isOpen} onClose={onClose}>
      {isLoading && !stores?.length ? (
        <Flex h="xs" alignItems="center" justifyContent="center">
          <Spinner size="md" />
        </Flex>
      ) : (
        <Flex direction="column" gap={8}>
          <Heading size="md">{t("select store")}</Heading>
          <Flex direction="column" gap={3} align="stretch">
            {storesToShow.map((store) => (
              <Button
                key={store.id}
                fontSize="sm"
                variant="outline"
                isLoading={isSubmitting && store.id === selectedStoreId}
                onClick={() => {
                  setSelectedStoreId(store.id);
                  handleOnClick(store.id);
                }}
              >
                {store.name}
              </Button>
            ))}
            {from !== "portal" && (
              <Button
                fontSize="sm"
                isLoading={isSubmitting && !selectedStoreId}
                onClick={() => {
                  setSelectedStoreId(undefined);
                  handleOnClick();
                }}
              >
                {t("go to portal")}
              </Button>
            )}
          </Flex>
        </Flex>
      )}
    </Modal>
  );
};

export default StoreSelectionModal;
