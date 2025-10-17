import { QueryKey } from "@tanstack/react-query";
import { useState } from "react";

import TaskForm from "@/components/TaskForm";
import { TaskFormValues } from "@/components/TaskForm/types";

import { queryClient } from "@/services/client";
import { useEditScheduleTaskMutation } from "@/services/schedule";

import Schedule, { ScheduleTask } from "@/types/schedule";

interface EditFormProps {
  schedule: Schedule;
  task: ScheduleTask;
  scheduleQueryKey: QueryKey;
  onCancel: () => void;
}

const EditForm = ({
  schedule,
  task,
  scheduleQueryKey,
  onCancel,
}: EditFormProps) => {
  const editTaskMutation = useEditScheduleTaskMutation();

  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = (values: TaskFormValues) => {
    setIsLoading(true);

    editTaskMutation.mutate(
      {
        scheduleId: schedule.id,
        taskId: task.id,
        ...values,
      },
      {
        onSettled: () => setIsLoading(false),
        onSuccess: ({ response }) => {
          onCancel();
          queryClient.setQueryData(scheduleQueryKey, response);
        },
      },
    );
  };

  return (
    <TaskForm
      defaultValues={{
        name: {
          sv_SE: task.translations?.sv_SE?.name?.value,
          en_US: task.translations?.en_US?.name?.value,
        },
        description: {
          sv_SE: task.translations?.sv_SE?.description?.value,
          en_US: task.translations?.en_US?.description?.value,
        },
      }}
      onSubmit={handleSubmit}
      onCancel={onCancel}
      isLoading={isLoading}
    />
  );
};

export default EditForm;
