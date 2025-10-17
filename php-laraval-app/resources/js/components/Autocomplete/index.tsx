import * as _ from "lodash-es";
import { forwardRef } from "react";

import MultipleAutocomplete, { MultipleAutocompleteProps } from "./Multiple";
import SingleAutocomplete, { SingleAutocompleteProps } from "./Single";

interface AutocompleteVariantProps {
  single: SingleAutocompleteProps & { multiple?: false };
  multiple: MultipleAutocompleteProps & { multiple: true };
}

type AutocompleteProps =
  AutocompleteVariantProps[keyof AutocompleteVariantProps];

const Autocomplete = forwardRef<HTMLInputElement, AutocompleteProps>(
  (props, ref) => {
    return props.multiple ? (
      <MultipleAutocomplete ref={ref} {...props} />
    ) : (
      <SingleAutocomplete ref={ref} {..._.omit(props, ["multiple"])} />
    );
  },
);

export default Autocomplete;
