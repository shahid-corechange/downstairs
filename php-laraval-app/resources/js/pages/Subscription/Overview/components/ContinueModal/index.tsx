import { Page } from "@inertiajs/core";
import { router } from "@inertiajs/react";
import { useState } from "react";
import { useTranslation } from "react-i18next";

import AlertDialog from "@/components/AlertDialog";
import ScheduleCollisionModal from "@/components/ScheduleCollisionModal";

import Schedule from "@/types/schedule";
import Subscription from "@/types/subscription";

import { PageProps } from "@/types";

interface ContinueModalProps {
  data?: Subscription;
  isOpen: boolean;
  onClose: () => void;
}

const ContinueModal = ({ data, isOpen, onClose }: ContinueModalProps) => {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);
  const [collidedSchedules, setCollidedSchedules] = useState<Schedule[]>([]);

  const handleContinue = () => {
    setIsLoading(true);
    router.post(`/customers/subscriptions/${data?.id}/continue`, undefined, {
      onFinish: () => setIsLoading(false),
      onSuccess: (page) => {
        const {
          flash: { error, errorPayload },
        } = (
          page as Page<PageProps<Record<string, unknown>, unknown, Schedule[]>>
        ).props;

        if (error) {
          setCollidedSchedules(errorPayload ?? []);
          return;
        }

        onClose();
      },
    });
  };

  return (
    <>
      <AlertDialog
        title={t("continue subscription")}
        confirmButton={{
          isLoading,
          loadingText: t("please wait"),
        }}
        confirmText={t("continue")}
        isOpen={isOpen}
        onClose={onClose}
        onConfirm={handleContinue}
      >
        {t("subscription continue alert body")}
      </AlertDialog>
      <ScheduleCollisionModal
        isOpen={collidedSchedules.length > 0}
        onClose={() => setCollidedSchedules([])}
        data={collidedSchedules}
      />
    </>
  );
};

export default ContinueModal;
