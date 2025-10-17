import { Text } from "@chakra-ui/react";
import { Page } from "@inertiajs/core";
import { router } from "@inertiajs/react";
import { useMemo, useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import AlertDialog from "@/components/AlertDialog";

import ScheduleDeviation from "@/types/scheduleDeviation";
import ScheduleEmployee from "@/types/scheduleEmployee";

import { toDayjs } from "@/utils/datetime";

import { PageProps } from "@/types";

import { FormValues } from "../../types";

interface ConfirmationModalProps {
  scheduleEmployees: ScheduleEmployee[];
  formValues?: FormValues;
  deviation?: ScheduleDeviation;
  onSuccess: () => void;
  onClose: () => void;
}

const ConfirmationModal = ({
  formValues,
  deviation,
  scheduleEmployees,
  onSuccess,
  onClose,
}: ConfirmationModalProps) => {
  const { t } = useTranslation();
  const [isLoading, setIsLoading] = useState(false);

  const showIncompleteAttendanceAlert = useMemo(() => {
    return scheduleEmployees.some(
      (employee) => !employee.startAt || !employee.endAt,
    );
  }, [scheduleEmployees]);

  const canHandle = useMemo(() => {
    return !showIncompleteAttendanceAlert
      ? true
      : deviation?.schedule?.endAt
      ? toDayjs(deviation.schedule.endAt).isBefore(toDayjs())
      : false;
  }, [deviation, showIncompleteAttendanceAlert]);

  const handleSubmit = () => {
    setIsLoading(true);
    const { actualQuarters, items } = formValues ?? {};

    router.post(
      `/deviations/${deviation?.id}/handle`,
      {
        actualQuarters,
        items:
          deviation?.schedule?.addonSummaries?.map((item) => ({
            id: item.id,
            isCharge: items?.includes(`${item.id}`),
          })) ?? [],
      },
      {
        onFinish: () => setIsLoading(false),
        onSuccess: (page) => {
          const {
            flash: { error },
          } = (page as Page<PageProps>).props;

          if (error) {
            return;
          }

          onSuccess();
        },
      },
    );
  };

  return (
    <AlertDialog
      title={t("handle deviation")}
      size="2xl"
      confirmButton={{
        isLoading: isLoading,
        loadingText: t("please wait"),
        hidden: !canHandle,
      }}
      confirmText={t("handle")}
      isOpen={!!formValues}
      onClose={onClose}
      onConfirm={() => canHandle && handleSubmit()}
    >
      {!canHandle && (
        <Alert
          status="info"
          title={t("info")}
          message={t("unable to handle deviation info")}
          fontSize="small"
          mb={6}
        />
      )}
      {showIncompleteAttendanceAlert && (
        <Alert
          status="warning"
          title={t("warning")}
          message={t("incomplete attendance warning")}
          fontSize="small"
          mb={6}
        />
      )}
      {formValues?.actualQuarters === 0 && (
        <Alert
          status="warning"
          title={t("warning")}
          message={t("zero actual quarters warning")}
          fontSize="small"
          mb={6}
        />
      )}
      <Text>
        <Trans i18nKey="handle deviation confirmation message" />
      </Text>
    </AlertDialog>
  );
};

export default ConfirmationModal;
