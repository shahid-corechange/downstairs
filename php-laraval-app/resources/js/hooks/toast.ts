import { useToast } from "@chakra-ui/react";
import { usePage } from "@inertiajs/react";
import { useEffect } from "react";
import { useTranslation } from "react-i18next";

import { PageProps } from "@/types";

export const useFlashToast = () => {
  const { flash } = usePage<PageProps>().props;
  const { t } = useTranslation();
  const toast = useToast({ containerStyle: { fontSize: "sm" } });

  useEffect(() => {
    let status: "success" | "error" | undefined;

    if (flash.success) {
      status = "success";
    } else if (flash.error) {
      status = "error";
    }

    if (status) {
      toast({
        variant: "solid",
        position: "top-right",
        title: t(status),
        description: status === "success" ? flash.success : flash.error,
        status,
      });
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [flash]);
};
