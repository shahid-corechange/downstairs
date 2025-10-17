import { router } from "@inertiajs/react";
import { useState } from "react";

import TaskForm from "@/components/TaskForm";
import { TaskFormValues } from "@/components/TaskForm/types";

import Subscription from "@/types/subscription";

interface AddFormProps {
  subscription?: Subscription;
  onCancel: () => void;
  onRefetch: () => void;
}

const AddForm = ({ subscription, onCancel, onRefetch }: AddFormProps) => {
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = (values: TaskFormValues) => {
    setIsLoading(true);

    router.post(`/customers/subscriptions/${subscription?.id}/tasks`, values, {
      onFinish: () => setIsLoading(false),
      onSuccess: () => {
        onCancel();
        onRefetch();
      },
    });
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
