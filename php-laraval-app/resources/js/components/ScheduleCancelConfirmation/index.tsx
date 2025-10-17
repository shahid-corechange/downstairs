import { useMemo, useState } from "react";
import { useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import AlertDialog from "@/components/AlertDialog";

import { useCancelScheduleMutation } from "@/services/schedule";

import { Response } from "@/types/api";
import Schedule from "@/types/schedule";

interface ScheduleCancelConfirmationProps {
  isOpen: boolean;
  creditRefundTimeWindow: number;
  onSuccess: (data: Schedule, response: Response<Schedule>) => void;
  onClose: () => void;
  schedule: Schedule;
}

const ScheduleCancelConfirmation = ({
  schedule,
  creditRefundTimeWindow,
  isOpen,
  onSuccess,
  onClose,
}: ScheduleCancelConfirmationProps) => {
  const { t } = useTranslation();

  const laundryScheduleType = useMemo(() => {
    if (
      !schedule.detail?.laundryOrderId ||
      !("laundryType" in schedule.detail)
    ) {
      return undefined;
    }

    return schedule.detail.laundryType;
  }, [schedule]);

  const cancelScheduleMutation = useCancelScheduleMutation();

  const [action, setAction] = useState<"cancel" | "refund">("cancel");
  const [isLoading, setIsLoading] = useState(false);

  const handleCancel = (refund: boolean) => {
    setAction(refund ? "refund" : "cancel");
    setIsLoading(true);

    cancelScheduleMutation.mutate(
      {
        scheduleId: schedule.id,
        refund,
      },
      {
        onSettled: () => {
          setIsLoading(false);
        },
        onSuccess: ({ data, response }) => {
          onSuccess(data, response);
          onClose();
        },
      },
    );
  };

  return (
    <AlertDialog
      title={t("cancel schedule")}
      size="2xl"
      secondaryButton={{
        colorScheme: "brand",
        tooltip: t("cancel schedule with refund"),
        isLoading: isLoading && action === "refund",
        isDisabled: isLoading && action !== "refund",
        loadingText: t("please wait"),
      }}
      confirmButton={{
        tooltip: t("cancel schedule without refund"),
        isLoading: isLoading && action === "cancel",
        isDisabled: isLoading && action !== "cancel",
        colorScheme: "red",
        loadingText: t("please wait"),
      }}
      cancelText={t("close")}
      secondaryText={schedule.refund ? t("refund") : undefined}
      confirmText={t("cancel")}
      isOpen={isOpen}
      onClose={onClose}
      onSecondary={() => handleCancel(true)}
      onConfirm={() => handleCancel(false)}
    >
      <Alert
        status="info"
        title={t("info")}
        message={t("schedule cancel alert info")}
        fontSize="small"
        mb={6}
      />
      {schedule.refund ? (
        <Alert
          status="info"
          title={t("info")}
          message={t("schedule cancel refund alert info", {
            amount: schedule.refund.amount,
          })}
          fontSize="small"
          mb={6}
        />
      ) : (
        <Alert
          status="info"
          title={t("info")}
          message={t("schedule cancel no refund alert info", {
            refundTimeWindow: creditRefundTimeWindow,
          })}
          fontSize="small"
          mb={6}
        />
      )}
      {laundryScheduleType && (
        <Alert
          status="warning"
          title={t("warning")}
          message={t("schedule laundry cancel alert warning", {
            detail: laundryScheduleType,
          })}
          fontSize="small"
          mb={6}
        />
      )}
      {t("schedule cancel alert body")}
    </AlertDialog>
  );
};

export default ScheduleCancelConfirmation;
