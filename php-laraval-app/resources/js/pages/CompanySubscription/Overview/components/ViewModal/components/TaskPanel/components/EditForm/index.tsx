import { router } from "@inertiajs/react";
import { useState } from "react";

import TaskForm from "@/components/TaskForm";
import { TaskFormValues } from "@/components/TaskForm/types";

import CustomTask from "@/types/customTask";
import Subscription from "@/types/subscription";

interface EditFormProps {
  subscription?: Subscription;
  task: CustomTask;
  onCancel: () => void;
  onRefetch: () => void;
}

const EditForm = ({
  subscription,
  task,
  onCancel,
  onRefetch,
}: EditFormProps) => {
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = (values: TaskFormValues) => {
    setIsLoading(true);

    router.patch(
      `/companies/subscriptions/${subscription?.id}/tasks/${task.id}`,
      values,
      {
        onFinish: () => setIsLoading(false),
        onSuccess: () => {
          onCancel();
          onRefetch();
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
