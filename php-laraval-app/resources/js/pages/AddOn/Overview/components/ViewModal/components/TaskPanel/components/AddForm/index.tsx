import { router } from "@inertiajs/react";
import { useState } from "react";

import TaskForm from "@/components/TaskForm";
import { TaskFormValues } from "@/components/TaskForm/types";

import Addon from "@/types/addon";

interface AddFormProps {
  addOn?: Addon;
  onCancel: () => void;
}

const AddForm = ({ addOn, onCancel }: AddFormProps) => {
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = (values: TaskFormValues) => {
    setIsLoading(true);

    router.post(`/addons/${addOn?.id}/tasks`, values, {
      onFinish: () => setIsLoading(false),
      onSuccess: onCancel,
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
