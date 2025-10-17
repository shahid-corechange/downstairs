import { QueryKey } from "@tanstack/react-query";
import { useState } from "react";

import TaskForm from "@/components/TaskForm";
import { TaskFormValues } from "@/components/TaskForm/types";

import { queryClient } from "@/services/client";
import { useAddScheduleTaskMutation } from "@/services/schedule";

import Schedule from "@/types/schedule";

interface AddFormProps {
  schedule: Schedule;
  scheduleQueryKey: QueryKey;
  onCancel: () => void;
}

const AddForm = ({ schedule, scheduleQueryKey, onCancel }: AddFormProps) => {
  const addTaskMutation = useAddScheduleTaskMutation();

  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = (values: TaskFormValues) => {
    setIsLoading(true);

    addTaskMutation.mutate(
      {
        scheduleId: schedule.id,
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
      onSubmit={handleSubmit}
      onCancel={onCancel}
      isLoading={isLoading}
    />
  );
};

export default AddForm;
