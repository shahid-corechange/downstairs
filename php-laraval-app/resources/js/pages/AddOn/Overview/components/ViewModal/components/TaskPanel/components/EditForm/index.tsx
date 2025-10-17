import { router } from "@inertiajs/react";
import { useState } from "react";

import TaskForm from "@/components/TaskForm";
import { TaskFormValues } from "@/components/TaskForm/types";

import Addon from "@/types/addon";
import CustomTask from "@/types/customTask";

interface EditFormProps {
  addOn?: Addon;
  task: CustomTask;
  onCancel: () => void;
}

const EditForm = ({ addOn, task, onCancel }: EditFormProps) => {
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = (values: TaskFormValues) => {
    setIsLoading(true);

    router.patch(`/addons/${addOn?.id}/tasks/${task.id}`, values, {
      onFinish: () => setIsLoading(false),
      onSuccess: onCancel,
    });
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
