import { Flex } from "@chakra-ui/react";
import { router, usePage } from "@inertiajs/react";
import { useMemo } from "react";
import { useTranslation } from "react-i18next";

import Autocomplete from "@/components/Autocomplete";

import { hasPermission } from "@/utils/authorization";

import { PageProps } from "@/types";

const StoreSelector = () => {
  const { t } = useTranslation();
  const { storeId, stores } = usePage<PageProps>().props;

  const storeOptions = useMemo(() => {
    const allStores =
      stores?.map((store) => ({
        label: store.name,
        value: store.id,
      })) ?? [];

    const portal = hasPermission("access portal")
      ? [
          {
            label: t("portal"),
            value: "",
          },
        ]
      : [];

    return [...allStores, ...portal];
  }, [stores, t]);

  const handleStoreChange = (value: string) => {
    router.post("/change-store", {
      storeId: value === "" ? undefined : value,
    });
  };

  return (
    <Flex alignItems="center">
      <Autocomplete
        options={storeOptions ?? []}
        value={storeId ?? ""}
        onChange={(e) => handleStoreChange(e.target.value)}
        placeholder={t("select a store")}
        cursor="pointer"
        size="xs"
        inputContainer={{
          border: "1px solid black",
          borderRadius: "md",
        }}
      />
    </Flex>
  );
};

export default StoreSelector;
