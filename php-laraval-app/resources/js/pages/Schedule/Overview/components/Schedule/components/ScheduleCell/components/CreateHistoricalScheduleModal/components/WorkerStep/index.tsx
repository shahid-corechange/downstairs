import { useConst } from "@chakra-ui/react";
import { Dayjs } from "dayjs";
import { useEffect, useMemo, useState } from "react";
import { useTranslation } from "react-i18next";
import { AiOutlineCalendar } from "react-icons/ai";
import { LuTrash } from "react-icons/lu";

import AuthorizationGuard from "@/components/AuthorizationGuard";
import Autocomplete from "@/components/Autocomplete";
import { AutocompleteOption } from "@/components/Autocomplete/types";
import DataTable from "@/components/DataTable";
import { useWizard } from "@/components/Wizard/hooks";

import { DATE_FORMAT, SIMPLE_TIME_FORMAT } from "@/constants/datetime";

import { usePageModal } from "@/hooks/modal";

import { useGetAvailableWorkers } from "@/services/schedule";

import Team from "@/types/team";
import User from "@/types/user";

import { StepsValues, WorkerAttendance, WorkerFormValues } from "../../types";
import getColumns from "./column";
import EditAttendanceModal from "./components/EditAttendanceModal";

interface WorkerStepProps {
  team: Team;
  startAt: Dayjs;
}

const WorkerStep = ({ team, startAt }: WorkerStepProps) => {
  const { t } = useTranslation();
  const { modal, modalData, openModal, closeModal } = usePageModal<
    WorkerAttendance,
    "editAttendance"
  >();

  const { stepsValues, setStepValues, isValidating, onValidateSuccess } =
    useWizard<StepsValues, WorkerFormValues>();

  const [workers, setWorkers] = useState<User[]>(
    stepsValues[1].workers ?? team.users,
  );
  const [attendances, setAttendances] = useState<WorkerAttendance[]>(
    stepsValues[1].attendances ??
      (team.users ?? []).map((user) => ({
        userId: user.id,
        startAt: stepsValues[0].utcStartAt.toISOString(),
        endAt: stepsValues[0].utcEndAt.toISOString(),
      })),
  );
  const [selectedWorkerId, setSelectedWorkerId] = useState<number>();
  const columns = useConst(getColumns(team, t));

  const baseWorkerIds = useMemo(
    () => (team.users ?? []).map((user) => user.id),
    [team],
  );

  const availableWorkers = useGetAvailableWorkers(
    stepsValues[0].utcStartAt,
    stepsValues[0].utcEndAt,
    baseWorkerIds,
    {
      request: {
        only: ["id", "fullname"],
      },
    },
  );
  const allWorkers = [...(team.users ?? []), ...(availableWorkers.data ?? [])];

  const availableWorkerOptions = useMemo(() => {
    return allWorkers.reduce<AutocompleteOption[]>((acc, worker) => {
      if (!workers.some((w) => w.id === worker.id)) {
        acc.push({
          value: worker.id,
          label: worker.fullname,
        });
      }
      return acc;
    }, []);
  }, [allWorkers, team, workers]);

  const handleAddWorker = (workerId: number) => {
    const worker = allWorkers.find((worker) => worker.id === workerId);

    if (!worker) {
      return;
    }

    setWorkers((prevState) => [...prevState, worker]);

    const stepValue: WorkerAttendance = {
      userId: worker.id,
      startAt: stepsValues[0].utcStartAt.toISOString(),
      endAt: stepsValues[0].utcEndAt.toISOString(),
    };

    setAttendances((prevState) => [...prevState, stepValue]);
    setSelectedWorkerId(worker.id);
  };

  const handleRemoveWorker = (workerId: number) => {
    setWorkers((prevState) =>
      prevState.filter((worker) => worker.id !== workerId),
    );
    setAttendances((prevState) =>
      prevState.filter((worker) => worker.userId !== workerId),
    );
  };

  const handleCloseEditAttendance = () => {
    closeModal();
    setSelectedWorkerId(undefined);
  };

  const handleEditAttendance = (stepValue: WorkerAttendance) => {
    setAttendances((prevState) => {
      const index = prevState.findIndex(
        (item) => item.userId === stepValue.userId,
      );
      const newValues = [...prevState];

      if (index > -1) {
        newValues[index] = stepValue;
      } else {
        newValues.push(stepValue);
      }

      return newValues;
    });
    setSelectedWorkerId(undefined);
  };

  useEffect(() => {
    const attendance = attendances.find(
      (item) => item.userId === selectedWorkerId,
    );
    openModal("editAttendance", attendance);
  }, [selectedWorkerId, attendances]);

  useEffect(() => {
    if (isValidating) {
      const { quarters } = stepsValues[0];
      const calendarQuarters = Math.ceil(quarters / (workers.length ?? 1));
      const endAt = startAt.add(calendarQuarters * 15, "minute");

      setStepValues(0, {
        ...stepsValues[0],
        utcEndAt: endAt.utc(),
        endAt: endAt.format(DATE_FORMAT),
        endTimeAt: endAt.format(SIMPLE_TIME_FORMAT),
      });
      onValidateSuccess({ attendances, workers });
    }
  }, [isValidating]);

  return (
    <>
      <AuthorizationGuard permissions="schedule workers create">
        <Autocomplete
          options={availableWorkerOptions}
          placeholder={t("type worker name")}
          onChange={(e) => handleAddWorker(Number(e.target.value))}
          container={{ my: 4 }}
        />
      </AuthorizationGuard>
      <DataTable
        data={workers}
        columns={columns}
        size="md"
        searchable={false}
        filterable={false}
        paginatable={false}
        actions={[
          {
            label: t("remove"),
            icon: LuTrash,
            colorScheme: "red",
            color: "red.500",
            _dark: { color: "red.200" },
            isHidden: workers.length === 1,
            onClick: (row) => handleRemoveWorker(row.original.id),
          },
          {
            label: t("edit attendance"),
            icon: AiOutlineCalendar,
            onClick: (row) => setSelectedWorkerId(row.original.id),
          },
        ]}
      />
      {modalData && (
        <EditAttendanceModal
          data={modalData}
          isOpen={modal === "editAttendance"}
          onClose={handleCloseEditAttendance}
          onSubmit={handleEditAttendance}
        />
      )}
    </>
  );
};

export default WorkerStep;
