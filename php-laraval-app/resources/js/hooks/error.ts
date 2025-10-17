import { useEffect } from "react";

import { ErrorResponse } from "@/types/api";

let validationError: Record<string, string> = {};
let error: Record<string, unknown> = {};

const defaultOptions = {
  clearOnUnmount: true,
};

interface UseErrorOptionsProps {
  clearOnUnmount?: boolean;
}

export const useError = (options?: UseErrorOptionsProps) => {
  const mergedOptions = { ...defaultOptions, ...options };

  const getErrors = <
    T extends Record<string, unknown> = Record<string, unknown>,
  >() => {
    let type: "validation" | "other" = "validation";

    if (Object.keys(error).length > 0) {
      type = "other";
    }

    return { type, validationError, error: error as T };
  };

  const consumeErrors = <
    T extends Record<string, unknown> = Record<string, unknown>,
  >() => {
    const errors = getErrors<T>();
    clearErrors();

    return errors;
  };

  const clearErrors = () => {
    validationError = {};
    error = {};
  };

  useEffect(() => {
    if (mergedOptions.clearOnUnmount) {
      return clearErrors;
    }
  }, []);

  return { validationError, getErrors, consumeErrors, clearErrors };
};

export const setError = (err: ErrorResponse) => {
  if (!err.response?.data.error) {
    return;
  }

  const { code, errors } = err.response.data.error;

  if (code === 422) {
    validationError = Object.entries(errors).reduce<Record<string, string>>(
      (acc, item) => {
        const [key, value] = item;

        if (
          Array.isArray(value) &&
          value.length > 0 &&
          typeof value[0] === "string"
        ) {
          acc[key] = value[0];
        } else if (typeof value === "string") {
          acc[key] = value;
        }

        return acc;
      },
      {},
    );
    error = {};

    return;
  }

  validationError = {};
  error = errors;
};
