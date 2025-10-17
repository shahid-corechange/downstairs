import * as _ from "lodash-es";
import React, { useEffect, useMemo } from "react";

import { useContext } from "@/hooks/context";

import { InputValue } from "@/types";

import { AutocompleteOption } from "../Autocomplete/types";
import { ComposerRowContext, InputComposerContext } from "./contexts";
import { InputComposerState } from "./types";

interface ComposerTemplateChildrenProps {
  rows: InputComposerState[`rows`];
  index: number;
  value: InputValue;
  options: (string | AutocompleteOption)[];
  onChange: React.ChangeEventHandler<
    HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement
  >;
}

export interface ComposerTemplateProps {
  name: string;
  children:
    | React.ReactElement
    | ((props: ComposerTemplateChildrenProps) => React.ReactElement);
  options?: (string | AutocompleteOption)[];
  defaultValue?: InputValue;
  unique?: boolean;
}

const ComposerTemplate = ({
  name,
  children,
  options,
  defaultValue,
  unique,
}: ComposerTemplateProps) => {
  const { rows } = useContext(InputComposerContext);
  const { index, getValue, onChange } = useContext(ComposerRowContext);
  const value = getValue(name);

  const filteredOptions = useMemo(() => {
    if (!options) return [];

    if (unique) {
      return options.filter((option) => {
        return !rows.some((row) => {
          return (
            row[name] ===
              (typeof option === "string" ? option : option.value) &&
            row[name] !== value
          );
        });
      });
    }

    return options;
  }, [name, rows, options, value, unique]);

  useEffect(() => {
    if (defaultValue !== undefined && !value) {
      onChange(name, defaultValue);
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    if (value && options) {
      const optionValues = options.map((option) =>
        typeof option === "string" ? option : option.value,
      );
      if (!optionValues.includes(value as string | number | boolean)) {
        onChange(name, undefined);
      }
    }
  }, [options, value, name, onChange]);

  if (_.isFunction(children)) {
    return children({
      rows,
      index,
      value,
      options: filteredOptions,
      onChange: (event) => onChange(name, event.target.value),
    });
  }

  return React.cloneElement(children, {
    value,
    options: filteredOptions,
    onChange: (
      event: React.ChangeEvent<
        HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement
      >,
    ) => onChange(name, event.target.value),
  });
};

export default ComposerTemplate;
