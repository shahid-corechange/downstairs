import { Text } from "@chakra-ui/react";
import { useMemo, useState } from "react";
import { Trans, useTranslation } from "react-i18next";

import Alert from "@/components/Alert";
import AlertDialog from "@/components/AlertDialog";

import { DATE_ATOM_FORMAT } from "@/constants/datetime";

import { useError } from "@/hooks/error";

import useScheduleStore from "@/pages/Schedule/Overview/store";
import { WorkerCollisionError } from "@/pages/Schedule/Overview/types";

import { useRescheduleMutation } from "@/services/schedule";

import { toDayjs } from "@/utils/datetime";

import WorkerCollisionModal from "../WorkerCollisionModal";

const RescheduleConfirmation = () => {
  const { t } = useTranslation();

  const rescheduleMutation = useRescheduleMutation();
  const { getErrors } = useError();

  const [action, setAction] = useState<"notify" | "silent">("notify");
  const schedule = useScheduleStore((state) => state.draggedSchedule);
  const target = useScheduleStore((state) => state.dragTarget);
  const selectedDate = useScheduleStore((state) => state.selectedDate);
  const setDraggedSchedule = useScheduleStore(
    (state) => state.setDraggedSchedule,
  );
  const setDraggedScheduleRect = useScheduleStore(
    (state) => state.setDraggedScheduleRect,
  );
  const setDragTarget = useScheduleStore((state) => state.setDragTarget);
  const updateSchedule = useScheduleStore((state) => state.updateSchedule);

  const [isLoading, setIsLoading] = useState(false);
  const [isWorkerColliding, setIsWorkerColliding] = useState(false);

  const prevStartAt = toDayjs(schedule?.startAt);
  const prevEndAt = toDayjs(schedule?.endAt);
  const newStartAt = toDayjs(
    target
      ? selectedDate
          .weekday(target.dayIndex)
          .format(`YYYY-MM-DDT${target.time}:00Z`)
      : prevStartAt.format(DATE_ATOM_FORMAT),
  );
  const newEndAt = newStartAt.add((target?.duration || 0) * 15, "minute");

  const handleClose = () => {
    setDraggedSchedule(undefined);
    setDraggedScheduleRect(undefined);
    setDragTarget(undefined);
    setIsWorkerColliding(false);
  };

  const handleReschedule = (isNotify: boolean) => {
    if (!schedule || !target) {
      return;
    }

    setAction(isNotify ? "notify" : "silent");
    setIsLoading(true);

    rescheduleMutation.mutate(
      {
        scheduleId: schedule.id,
        startAt: newStartAt.utc().format(),
        teamId: target.teamId,
        isNotify,
      },
      {
        onSettled: () => {
          setIsLoading(false);
        },
        onSuccess: ({ data }) => {
          handleClose();
          updateSchedule(data);
        },
        onError: () => {
          const { type, error } = getErrors<Partial<WorkerCollisionError>>();

          if (
            type === "other" &&
            error.scheduleCollidedWorkers &&
            error.workerCollisions
          ) {
            setIsWorkerColliding(true);
          }
        },
      },
    );
  };

  const laundryType = useMemo(() => {
    if (!schedule?.detail || !("laundryType" in schedule.detail)) {
      return;
    }

    return t(schedule.detail.laundryType ?? "");
  }, [schedule]);

  if (!schedule || !target) {
    return null;
  }

  return (
    <>
      <AlertDialog
        title={t("reschedule confirmation")}
        size="xl"
        confirmButton={{
          tooltip: t("reschedule with sending notifications"),
          isLoading: isLoading && action === "notify",
          isDisabled: isLoading && action !== "notify",
          loadingText: t("please wait"),
        }}
        secondaryButton={{
          colorScheme: "red",
          tooltip: t("reschedule without sending notifications"),
          isLoading: isLoading && action === "silent",
          isDisabled: isLoading && action !== "silent",
          loadingText: t("please wait"),
        }}
        confirmText={t("reschedule")}
        secondaryText={t("silent reschedule")}
        isOpen={!!schedule && !!target && !isWorkerColliding}
        onClose={() => (isWorkerColliding ? null : handleClose())}
        onSecondary={() => handleReschedule(false)}
        onConfirm={() => handleReschedule(true)}
      >
        {schedule?.team?.id !== target?.teamId && (
          <Alert
            status="warning"
            title={t("warning")}
            message={t("schedule reschedule different team alert body")}
            fontSize="small"
            mb={6}
          />
        )}
        {schedule?.isFixed && (
          <Alert
            status="warning"
            title={t("warning")}
            message={t("schedule reschedule fixed booking alert body")}
            fontSize="small"
            mb={6}
          />
        )}
        {laundryType && (
          <Alert
            status="warning"
            title={t("warning")}
            message={t("reschedule laundry schedule alert warning", {
              type: laundryType,
            })}
            fontSize="small"
            mb={6}
          />
        )}
        <Text fontSize="sm">
          <Trans
            i18nKey="schedule reschedule alert body"
            values={{
              previousTime: `${prevStartAt.format("LL")} (${prevStartAt.format(
                "LT",
              )} - ${prevEndAt.format("LT")})`,
              newTime: `${newStartAt.format("LL")} (${newStartAt.format(
                "LT",
              )} - ${newEndAt.format("LT")})`,
            }}
          />
        </Text>
      </AlertDialog>
      <WorkerCollisionModal
        startAt={newStartAt}
        endAt={newEndAt}
        scheduleId={schedule?.id}
        isOpen={isWorkerColliding}
        submitButtonLabel={t("continue reschedule")}
        onSubmit={() => handleReschedule(action === "notify")}
        onClose={handleClose}
      />
    </>
  );
};

export default RescheduleConfirmation;
