import { useMemo } from "react";

import { AutocompleteOption } from "@/components/Autocomplete/types";

export const useFilterOptions = (
  options: (string | AutocompleteOption)[],
  filter?: string,
) => {
  const filteredIndex = useMemo(
    () =>
      options.reduce((acc, option, index) => {
        if (filter) {
          const lowerFilter = filter.toLowerCase();

          if (
            typeof option === "string" &&
            option.toLowerCase().includes(lowerFilter)
          ) {
            acc.push(index);
          } else if (
            typeof option !== "string" &&
            option.label.toLowerCase().includes(lowerFilter)
          ) {
            acc.push(index);
          }
        } else {
          acc.push(index);
        }

        return acc;
      }, [] as number[]),
    [filter, options],
  );

  return filteredIndex;
};
