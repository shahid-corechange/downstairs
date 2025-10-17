import { router } from "@inertiajs/react";
import { useState } from "react";

import TaskForm from "@/components/TaskForm";
import { TaskFormValues } from "@/components/TaskForm/types";

import Service from "@/types/service";

interface AddFormProps {
  service?: Service;
  onCancel: () => void;
}

const AddForm = ({ service, onCancel }: AddFormProps) => {
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = (values: TaskFormValues) => {
    setIsLoading(true);

    router.post(`/services/${service?.id}/tasks`, values, {
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
