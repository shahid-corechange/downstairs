import { Text } from "@chakra-ui/react";

import { AutocompleteOption } from "@/components/Autocomplete/types";

import i18n from "./localization";

export const getTranslatedOptions = <T extends string | AutocompleteOption>(
  options: T[],
  transformer?: (option: T) => AutocompleteOption,
) => {
  return options.map((option) => {
    if (transformer) {
      return transformer(option);
    }

    if (typeof option === "string") {
      return { label: i18n.t(option), value: option };
    }

    return { label: i18n.t(option.label), value: option.value };
  }, [] as AutocompleteOption[]);
};

export const renderHighlightedText = (
  option: string | AutocompleteOption,
  filter?: string,
) => {
  const text = typeof option === "string" ? option : option.label;

  if (!filter) {
    return <Text noOfLines={2}>{text}</Text>;
  }

  const index = text.toLowerCase().indexOf(filter.toLowerCase());
  const startText = text.substring(0, index);
  const highlightedText = text.substring(index, index + filter.length);
  const endText = text.substring(index + filter.length);

  return (
    <Text noOfLines={2}>
      {startText}
      <strong>{highlightedText}</strong>
      {endText}
    </Text>
  );
};
